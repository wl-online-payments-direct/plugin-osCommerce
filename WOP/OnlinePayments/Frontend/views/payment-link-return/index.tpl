{use class="common\helpers\Html"}
<br>
<br>
<center>
    <div class="card">
        <div class="card-body">
            <div class="online-payments-waiting-page">
                <div class="online-payments-waiting-container">
                    <div class="online-payments-status-message" id="payment-status-message"
                         data-ajax-url="{$ajax_url}">
                        <h1>{$title}</h1>
                        <img src="{$loaderImageUrl}" title="Loading..." alt="Loading..." />
                    </div>

                    <div class="online-payments-contact-message" id="payment-contact-message" style="display: none;">
                        <div class="messages messages--warning">
                            <div class="messages__content container">
                                <p>{$pendingTransMsg}</p>
                                <p>
                                    {$pendingTransDetails}
                                    <a href="{$contactUsLink}">{$contactUsLink}</a>
                                </p>
                                {if $hostedCheckoutId or $paymentId }
                                <p>
                                    {$pendingTransInstructions}
                                </p>
                                    {if $paymentId }
                                        <div>
                                            <strong>{$paymentIdLabel}</strong>
                                        </div>
                                        <div>
                                            <p>{$paymentId}}</p>
                                        </div>
                                    {/if}
                                    {if $hostedCheckoutId }
                                        <div>
                                            <strong>{$hostedCheckoutIdLabel}</strong>
                                        </div>
                                        <div>
                                            <p>{$hostedCheckoutId}</p>
                                        </div>
                                    {/if}
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</center>