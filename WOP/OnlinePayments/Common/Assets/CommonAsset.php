<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Common\Assets;

use yii\web\AssetBundle;
/**
 * Common Asset Bundle.
 *
 * Shared assets (payment method logos, CSS, JS) used in both backend and frontend.
 *
 * @package OnlinePayments\Common\Assets
 */
class CommonAsset extends AssetBundle
{
    /**
     * @var string The source path containing shared assets
     */
    public $sourcePath = __DIR__ . '/../assets';
    /**
     * @var array List of CSS files to publish (optional)
     */
    public $css = [];
    /**
     * @var array List of JavaScript files to publish (optional)
     */
    public $js = [];
    /**
     * @var array Asset bundle dependencies
     */
    public $depends = [];
    /**
     * @var array Asset publishing options
     */
    public $publishOptions = ['forceCopy' => YII_DEBUG];
}
