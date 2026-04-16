<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Common\Controllers;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
trait ControllerViewPathResolver
{
    public function getViewPath()
    {
        $ref = new \ReflectionClass(get_class($this));
        $viewPath = dirname($ref->getFileName(), 2) . \DIRECTORY_SEPARATOR . 'views';
        return $viewPath . \DIRECTORY_SEPARATOR . ModuleHelper::removeModuleNamePrefix($this->id);
    }
}
