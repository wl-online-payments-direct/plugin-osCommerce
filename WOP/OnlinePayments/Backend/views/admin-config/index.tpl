{use class="common\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
    <!--=== Page Content ===-->
    <div class="btn-bar btn-bar-top after ">
        <div class="btn-left"><a href="{Yii::$app->urlManager->createUrl(['modules', 'platform_id' => $selected_platform_id, 'set'=> 'payment', 'type'=>'online'])}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
        </div>
        <div class="btn-right">
            <a class="btn btn-edit btn-no-margin" target="_blank" href="{Yii::$app->urlManager->createUrl(['modules/translation','platform_id'=>$selected_platform_id,'set'=> 'payment', 'module' => $module])}">{$smarty.const.IMAGE_BUTTON_TRANSLATE}</a>
        </div>
    </div>

    <div id="op-page" class="op-page">
        <main>
            <div class="opp-content-holder">
                <header id="op-main-header">
                </header>
                <main id="op-main-page-holder"></main>
                <div id="op-footer"></div>
            </div>
        </main>
        <div class="op-page-loader ops--hidden" id="op-spinner">
            <div class="op-loader opt--large">
                <span class="opp-spinner"></span>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        if (!{$brand.code}) {
            {$brand.code} = {};
        }

        let OnlinePaymentsFE = {$brand.code};

        OnlinePaymentsFE.translations = {
            default: {$translations.default|json_encode},
            current: {$translations.current|json_encode}
        };

        OnlinePaymentsFE.baseImgUrl = '{$baseLogoUrl}';

        OnlinePaymentsFE.brand = {$brand|json_encode};
        OnlinePaymentsFE.utilities.showLoader();

        const pageConfiguration = {
            connection: {
                getSettingsUrl: '{$urls.connection.getSettingsUrl}',
                submitUrl: '{$urls.connection.submitUrl}',
                disconnectUrl: '',
                webhooksUrl: '{$urls.connection.webhooksUrl}'
            },
            payments: {
                getAvailablePaymentsUrl: '{$urls.payments.getAvailablePaymentsUrl}',
                enableMethodUrl: '{$urls.payments.enableMethodUrl}',
                saveMethodConfigurationUrl: '{$urls.payments.saveMethodConfigurationUrl}',
                getMethodConfigurationUrl: '{$urls.payments.getMethodConfigurationUrl}',
                paymentMethodLogoUrl:  window.location.protocol + '//' + window.location.host +
                    OnlinePaymentsFE.baseImgUrl + '/payment_products/',
                getLanguagesUrl: '{$urls.payments.getLanguagesUrl}'
            },
            settings: {
                getGeneralSettingsUrl: '{$urls.settings.getGeneralSettingsUrl}',
                getPaymentStatusesUrl: '{$urls.settings.getPaymentStatusesUrl}',
                saveConnectionUrl: '{$urls.settings.saveConnectionUrl}',
                saveCardsSettingsUrl: '{$urls.settings.saveCardsSettingsUrl}',
                savePaymentSettingsUrl: '{$urls.settings.savePaymentSettingsUrl}',
                saveLogSettingsUrl: '{$urls.settings.saveLogSettingsUrl}',
                savePayByLinkSettingsUrl: '{$urls.settings.savePayByLinkSettingsUrl}',
                webhooksUrl: '{$urls.settings.webhooksUrl}',
                disconnectUrl: '{$urls.settings.disconnectUrl}',
                getRestrictionsUrl: '{$urls.settings.getRestrictionsUrl}',
                saveRestrictionsUrl: '{$urls.settings.saveRestrictionsUrl}'
            },
            monitoring: {
                getMonitoringLogsUrl: '{$urls.monitoring.getMonitoringLogsUrl}',
                getWebhookLogsUrl: '{$urls.monitoring.getWebhookLogsUrl}',
                downloadMonitoringLogsUrl: '{$urls.monitoring.downloadMonitoringLogsUrl}',
                downloadWebhookLogsUrl: '{$urls.monitoring.downloadWebhookLogsUrl}',
                page: 'webhooks'
            }
        };

        OnlinePaymentsFE.state = new OnlinePaymentsFE.StateController({
            brand: OnlinePaymentsFE.brand,
            storesUrl: '{$urls.stores.storesUrl}',
            connectionDetailsUrl: '{$urls.connection.getSettingsUrl}',
            currentStoreUrl: '{$urls.stores.currentStoreUrl}',
            stateUrl: '{$urls.integration.stateUrl}',
            versionUrl: '{$urls.version.versionUrl}',
            pageConfiguration: pageConfiguration
        });

        OnlinePaymentsFE.state.setStoreId('{$selected_platform_id}');
        OnlinePaymentsFE.state.display();
        OnlinePaymentsFE.utilities.hideLoader();
    });
</script>