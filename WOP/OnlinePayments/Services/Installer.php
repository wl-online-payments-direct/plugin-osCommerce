<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services;

use common\classes\Migration;
use common\helpers\Language;
use common\helpers\Translation;
use common\models\Modules;
use common\models\ModulesVisibility;
use common\models\OrdersStatus;
use common\models\OrdersStatusGroups;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Configuration\ConfigurationManager;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use yii\db\Schema;
/**
 * Class Installer.
 *
 * @package OnlinePayments\Services
 */
class Installer extends Migration
{
    public $compact = \true;
    public function install(): void
    {
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('entity'), $this->getTableColumnsDefinitions(9), null, ['entity_type,index_1']);
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('queue'), $this->getTableColumnsDefinitions(9), null, ['index_1,index_2,index_5', 'index_1,index_8']);
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('monitoring_logs'), $this->getTableColumnsDefinitions(9), null, ['index_1,index_2,index_7']);
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('webhook_logs'), $this->getTableColumnsDefinitions(9), null, ['index_1,index_2,index_7']);
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('tokens'), $this->getTableColumnsDefinitions(9), null, ['index_1,index_2']);
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('product_types'), $this->getTableColumnsDefinitions(9), null, ['index_1']);
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('payment_transactions'), $this->getTableColumnsDefinitions(9), null, ['index_1,index_2', 'index_1,index_3']);
        $this->createTableIfNotExists(ModuleHelper::getFullTableName('payment_transaction_locks'), $this->getTableColumnsDefinitions(9), null, ['unique:store_trans_merchant_ref_unique' => ['index_1', 'index_2', 'index_3']]);
        $this->addRefundedStatus();
        $this->addMultiLanguageTranslations();
    }
    public function remove(): void
    {
        $this->dropTableIfExists(ModuleHelper::getFullTableName('entity'));
        $this->dropTableIfExists(ModuleHelper::getFullTableName('queue'));
        $this->dropTableIfExists(ModuleHelper::getFullTableName('monitoring_logs'));
        $this->dropTableIfExists(ModuleHelper::getFullTableName('webhook_logs'));
        $this->dropTableIfExists(ModuleHelper::getFullTableName('tokens'));
        $this->dropTableIfExists(ModuleHelper::getFullTableName('product_types'));
        $this->dropTableIfExists(ModuleHelper::getFullTableName('payment_transactions'));
        $this->dropTableIfExists(ModuleHelper::getFullTableName('payment_transaction_locks'));
        Modules::deleteAll(['code' => ModuleHelper::getModuleConfig()->getModuleName()]);
        ModulesVisibility::deleteAll(['code' => ModuleHelper::getModuleConfig()->getModuleName()]);
    }
    private function getTableColumnsDefinitions(int $indexNum): array
    {
        $columns = ['id' => $this->bigPrimaryKey(), 'entity_type' => $this->string(255)->null()];
        for ($i = 1; $i <= $indexNum; $i++) {
            $columns['index_' . $i] = $this->string(191)->null();
        }
        $columns['data'] = $this->getDb()->getSchema()->createColumnSchemaBuilder('LONGTEXT')->null();
        return $columns;
    }
    private function addRefundedStatus()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::class);
        $languageId = Language::get_default_language_id();
        $existingStatus = OrdersStatus::find()->where(['orders_status_name' => 'Refunded', 'language_id' => $languageId, 'orders_status_groups_id' => OrdersStatusGroups::CANCELLED_GROUP])->one();
        if ($existingStatus) {
            $configurationManager->saveConfigValue('refundedStatusId', $existingStatus->orders_status_id, \false);
            return;
        }
        $languages = Language::get_languages(\true);
        $statusNames = [];
        foreach ($languages as $language) {
            $statusNames[$language['id']] = 'Refunded';
        }
        $params = [
            'automated' => 0,
            // 0 = manual, 1 = automated
            'orders_status_template' => '',
            'orders_status_template_confirm' => '',
            'orders_status_template_sms' => '',
            'order_evaluation_state_id' => 0,
            'orders_status_allocate_allow' => 0,
            'orders_status_release_deferred' => 0,
            'comment_template_id' => 0,
        ];
        $newStatusId = OrdersStatus::newOrdersStatusId();
        OrdersStatus::insertNew(OrdersStatusGroups::CANCELLED_GROUP, $statusNames, $params);
        $configurationManager->saveConfigValue('refundedStatusId', $newStatusId, \false);
    }
    private function addMultiLanguageTranslations(): void
    {
        $translations = json_decode(file_get_contents(__DIR__ . '/../../translations.json'), \true);
        // Get all active languages
        $languages = Language::get_languages(\true);
        foreach ($translations as $key => $languageValues) {
            foreach ($languages as $language) {
                $languageCode = strtolower($language['code']);
                // Use translation for specific language or fall back to English
                $value = $languageValues[$languageCode] ?? $languageValues['en'] ?? reset($languageValues);
                Translation::setTranslationValue(
                    ModuleHelper::getFullConstantName($key),
                    'payment',
                    // entity
                    $language['id'],
                    // language ID
                    $value
                );
            }
        }
        // Clear cache after adding translations
        Translation::resetCache();
    }
}
