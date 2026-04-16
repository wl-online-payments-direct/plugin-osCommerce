const worldlineop_tokenizer = (function () {
    let client, module, settings, token, submitButtonDisabled,
        $tokenizationContainer, $form, $submitButton, $selectedPaymentMethod;

    /**
     * Observer for the submit button disable status change because in multiple places in osCommerce (e.g. PP express)
     * button is enabled even though state is not valid and our payment method is selected
     * @type {MutationObserver}
     */
    const observer = new MutationObserver(function (mutationsList) {
        for (const mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                const isDisabled = mutation.target.disabled;
                if (isDisabled !== submitButtonDisabled) {
                    $submitButton.prop('disabled', submitButtonDisabled);
                }
            }
        }
    });

    function init (moduleName, config) {
        module = moduleName;
        settings = config;
        $tokenizationContainer = $(settings.tokenizationContainer);

        if (client) {
            client.destroy();
            client = null;
        }

        if ($tokenizationContainer.length === 0) {
            return;
        }

        $form = $tokenizationContainer.closest('form');
        if ($form.length === 0) {
            return;
        }

        $form.off('submit', formSSubmitHandler);

        $submitButton = $form.find('input[type="submit"], button[type="submit"]');
        observer.observe($submitButton[0], { attributes: true, attributeFilter: ['disabled'] });
        disableSubmitButton(false);

        $selectedPaymentMethod = $form.find(`#payment_method input[name=payment][value^=${module}_cc_]:checked`);
        if (!$selectedPaymentMethod.length) {
            observer.disconnect();

            return;
        }

        token = null;
        if ($selectedPaymentMethod.attr("value").indexOf(`_token_`) !== -1) {
            let idParts = $selectedPaymentMethod.attr("value").split('_token_');
            token = idParts[1] || null;
        }

        // Use given tokenization page url for stored tokens and grouped cards
        if (token || $selectedPaymentMethod.attr("value").indexOf(`_cc_cards`) !== -1) {
            initializeClient(settings.urls.hostedTokenizationPageUrl);

            return;
        }

        // Fetch card brand specific tokenization url for ungrouped cards
        crateHostedTokenizationSession().then((result) => {
            if (result.success && result.hostedTokenizationPageUrl) {
                initializeClient(result.hostedTokenizationPageUrl);
            }
        }).catch((err) => {
            observer.disconnect();
            console.error(err);
        });
    }

    function formSSubmitHandler(e) {
        if ($(`input[name=${module}-hosted_tokenization_id]`, $form).val().length > 0) {
            $(`input[name=${module}-color_depth]`, $form).val(screen.colorDepth);
            $(`input[name=${module}-screen_height]`, $form).val(screen.height);
            $(`input[name=${module}-screen_width]`, $form).val(screen.width);
            $(`input[name=${module}-timezone_offset_utc_minutes]`, $form).val(new Date().getTimezoneOffset());
            $(`input[name=${module}-java_enabled]`, $form).val(navigator.javaEnabled());

            return true;
        }

        e.preventDefault();

        client.submitTokenization().then(function (data) {
            if (data.success) {
                $(`input[name=${module}-hosted_tokenization_id]`, $form).val(data.hostedTokenizationId);
                $form.submit();
            }
        });
    }

    function initializeClient(hostedTokenizationPageUrl) {
        disableSubmitButton(true);

        client = new Tokenizer(
            hostedTokenizationPageUrl,
            $tokenizationContainer.attr('id'),
            {
                hideCardholderName: false,
                validationCallback: function (result) {
                    disableSubmitButton(!result.valid);
                },
                storePermanently: false
            }
        );
        client.initialize();
        if (token) {
            client.useToken(token);
        }

        $form.on('submit', formSSubmitHandler);
    }

    function disableSubmitButton(disable) {
        submitButtonDisabled = disable;
        $(window).trigger('disable-checkout-button', { name: module, value: disable});
        $submitButton.prop('disabled', disable);
    }

    async function crateHostedTokenizationSession() {
        return new Promise(function (resolve, reject) {
            fetch(settings.urls.createSession, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    selectedPaymentMethod: $selectedPaymentMethod.attr('value')
                })
            }).then((response) => {
                resolve(response.json());
            }).catch((err) => {
                reject(err);
            });
        });
    }

    return {
        init: init
    }
})();