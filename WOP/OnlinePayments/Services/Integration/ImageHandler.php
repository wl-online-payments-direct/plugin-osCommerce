<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\classes\platform_config;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
class ImageHandler
{
    public const AUTHORIZED_LOGO_EXTENSION = ['png', 'gif', 'jpg'];
    private const UPLOAD_DIR = 'images/';
    public static function saveImage(UploadedFile $file, string $fileName, string $storeId, string $mode): bool
    {
        $fileTypeKey = strtolower(pathinfo($file->name, \PATHINFO_EXTENSION));
        if (!in_array($fileTypeKey, self::AUTHORIZED_LOGO_EXTENSION)) {
            return \false;
        }
        $uploadPath = static::getUploadPath($storeId, $mode);
        if (!static::createDirectory($uploadPath)) {
            return \false;
        }
        $filePath = $uploadPath . $fileName . '.' . $fileTypeKey;
        if (static::fileExists($fileName, $storeId, $mode)) {
            static::removeImage($fileName, $storeId, $mode);
        }
        if (!$file->saveAs($filePath)) {
            return \false;
        }
        return \true;
    }
    public static function getImageUrl(string $fileName, string $storeId, string $mode): string
    {
        $platformConfig = new platform_config($storeId);
        $baseUrl = $platformConfig->getCatalogBaseUrl();
        $brand = ModuleHelper::getModuleConfig()->getBrand();
        $uploadPath = static::getUploadPath($storeId, $mode);
        $filePath = $uploadPath . $fileName;
        $imageUrl = $baseUrl . self::UPLOAD_DIR . $brand . '/' . $storeId . '/' . $mode . '/' . $fileName;
        if (file_exists($filePath . '.png')) {
            return $imageUrl . '.png';
        }
        if (file_exists($filePath . '.jpg')) {
            return $imageUrl . '.jpg';
        }
        if (file_exists($filePath . '.gif')) {
            return $imageUrl . '.gif';
        }
        return '';
    }
    public static function removeImage(string $fileName, string $storeId, string $mode)
    {
        $uploadPath = static::getUploadPath($storeId, $mode);
        $filePath = $uploadPath . $fileName;
        if (file_exists($filePath . '.png')) {
            unlink($filePath . '.png');
        }
        if (file_exists($filePath . '.jpg')) {
            unlink($filePath . '.jpg');
        }
        if (file_exists($filePath . '.gif')) {
            unlink($filePath . '.gif');
        }
    }
    public static function removeDirectoryForStore(string $storeId, string $mode): void
    {
        $uploadPath = static::getUploadPath($storeId, $mode);
        if (file_exists($uploadPath)) {
            $items = array_diff(scandir($uploadPath), ['.', '..']);
            foreach ($items as $item) {
                unlink($uploadPath . $item);
            }
            rmdir($uploadPath);
        }
    }
    protected static function getUploadPath(string $storeId, string $mode): string
    {
        $brand = ModuleHelper::getModuleConfig()->getBrand();
        return Yii::getAlias('@webroot') . '/../' . self::UPLOAD_DIR . '/' . $brand . '/' . $storeId . '/' . $mode . '/';
    }
    protected static function createDirectory(string $path): bool
    {
        return FileHelper::createDirectory($path, 0755);
    }
    protected static function fileExists(string $fileName, string $storeId, string $mode): bool
    {
        $path = static::getUploadPath($storeId, $mode) . $fileName;
        if (file_exists($path . '.png')) {
            return \true;
        }
        if (file_exists($path . '.jpg')) {
            return \true;
        }
        if (file_exists($path . '.gif')) {
            return \true;
        }
        return \false;
    }
}
