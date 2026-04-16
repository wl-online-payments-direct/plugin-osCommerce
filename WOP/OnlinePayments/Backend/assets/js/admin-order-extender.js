function worldlineop_AdminOrderExtender(module, amountErrorLabel) {
    jQuery(function ($) {
        function onTransactionsTableLoad() {
            $('[data-class="popupCredithistory"], [data-action="delete"]').hide();
            $('#popup-pay-now').hide();
            $('[data-action="void"]').prop('onclick', null).off('click').on('click', voidClickHandler);

            if ($(`#${module}_is_partial_payment`).data('partial')) {
                $('[data-action="refund"]').prop('onclick', null).off('click').on('click', refundClickHandler);
            } else {
                $('[data-action="refund"]').data('amount', $(`#${module}_max_refund_amount`).data('amount'));
            }

            $('[data-action="capture"]').data('amount', $(`#${module}_max_capture_amount`).data('amount'));
            var translations = {
                'TEXT_FRAUD_RESULT': $(`#${module}_fraud_result`).data('translation'),
                'TEXT_LIABILITY': $(`#${module}_liability`).data('translation'),
                'TEXT_THREE_DS_EXEMPTION_TYPE': $(`#${module}_three_ds_exemption`).data('translation'),
            };
            var $table = $('.order-payment-datatable');

            $table.find('tbody tr').each(function () {
                var $row = $(this);
                var $commentaryCell = $row.find('td').eq(5);

                if ($commentaryCell.length > 0) {
                    var commentary = $commentaryCell.html();

                    if (commentary) {
                        $.each(translations, function (key, value) {
                            var regex = new RegExp(key + ':', 'g');
                            commentary = commentary.replace(regex, value + ':');
                        });

                        $commentaryCell.html(commentary);
                    }
                }
            });
        }

        function voidClickHandler(event) {
            event.preventDefault();

            let el = $(this),
                id = el.data('id'),
                action = el.data('action'),
                prompt = el.data('prompt'),
                amount = $(`#${module}_max_void_amount`).data('amount');
            if (id && action && trans) {
                bootbox.prompt({
                    title: prompt,
                    value: amount,
                    callback: function (result) {
                        if (result !== null) {
                            if (parseFloat(result) > amount) {
                                bootbox.alert(amountErrorLabel);
                            } else {
                                trans.transactionalAction(id, result, action);
                            }
                        }

                    }
                });
            }
        }

        function refundClickHandler(event) {
            event.preventDefault();

            let el = $(this),
                id = el.data('id'),
                action = el.data('action'),
                amount = $(`#${module}_max_refund_amount`).data('amount'),
                formattedAmount = $(`#${module}_formatted_refund_amount`).data('amount'),
                partialPaymentNote = $(`#${module}_partial_payment_refund`).data('translation');

            if (id && action && trans) {
                let noteWithAmount = partialPaymentNote.replace('%s', formattedAmount);
                bootbox.confirm(noteWithAmount, function (result) {
                    if (result) {
                        trans.transactionalAction(id, amount, action);
                    }
                });
            }
        }

        $('[data-class="transactions-popup-box"]').popUp({
            opened: function () {
                const originalInitTable = window.initTable;
                window.initTable = function () {
                    originalInitTable();
                    onTransactionsTableLoad();
                };
                onTransactionsTableLoad();
            }
        });
    });
}