<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services;

use common\models\ModulesVisibility;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
class ModuleVisibilityService
{
    public function setModuleVisibility()
    {
        $moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
        $platformId = StoreContext::getInstance()->getStoreId();
        $moduleVisibility = ModulesVisibility::findOne(['code' => $moduleCode, 'platform_id' => $platformId]);
        if (!$moduleVisibility) {
            $moduleVisibility = new ModulesVisibility();
            $moduleVisibility->platform_id = $platformId;
            $moduleVisibility->code = $moduleCode;
        }
        $areas = [];
        if (!empty($moduleVisibility->area)) {
            $areas = explode(',', $moduleVisibility->area);
        }
        $areas = array_unique(array_merge($areas, ['admin', 'shop_order']));
        $moduleVisibility->area = implode(',', $areas);
        $moduleVisibility->save();
    }
}
