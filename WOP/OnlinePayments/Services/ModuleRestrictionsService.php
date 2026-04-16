<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services;

use common\helpers\Extensions;
use common\helpers\Group;
use common\models\ModulesVisibility;
use common\models\ModulesGroupsSettings;
use common\models\ModulesCountries;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use Throwable;
use yii\db\StaleObjectException;
class ModuleRestrictionsService
{
    private string $moduleCode;
    public function __construct()
    {
        $this->moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
    }
    /**
     * Get all available customer groups
     * Returns array of objects with 'id' and 'name' keys
     *
     * @return array [['id' => '1', 'name' => 'Basic'], ['id' => '2', 'name' => 'Advanced'], ...]
     */
    public function getAvailableGroups(): array
    {
        $groups = Group::get_customer_groups_list();
        $result = [];
        foreach ($groups as $id => $name) {
            $result[] = ['value' => (string) $id, 'label' => $name];
        }
        return $result;
    }
    /**
     * Get selected customer groups for this module
     *
     * @param int $platformId
     *
     * @return array Array of selected group IDs as strings
     */
    public function getSelectedGroups(int $platformId): array
    {
        $modulesGroups = ModulesGroupsSettings::findOne(['platform_id' => $platformId, 'code' => $this->moduleCode]);
        if (!$modulesGroups || empty($modulesGroups->group_list)) {
            return [];
        }
        return explode(',', $modulesGroups->group_list);
    }
    /**
     * Save selected customer groups
     *
     * @param int $platformId
     * @param array $groupIds Array of group IDs to save (empty array = no restrictions)
     *
     * @return bool
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function saveSelectedGroups(int $platformId, array $groupIds): bool
    {
        $modulesGroups = ModulesGroupsSettings::findOne(['platform_id' => $platformId, 'code' => $this->moduleCode]);
        // If no groups selected, delete the restriction (allow all groups)
        if (empty($groupIds)) {
            if (!is_null($modulesGroups)) {
                return $modulesGroups->delete() !== \false;
            }
            return \true;
        }
        // Create or update restriction
        if (is_null($modulesGroups)) {
            $modulesGroups = new ModulesGroupsSettings();
            $modulesGroups->platform_id = $platformId;
            $modulesGroups->code = $this->moduleCode;
        }
        $modulesGroups->group_list = implode(',', $groupIds);
        return $modulesGroups->save(\false);
    }
    /**
     * Get all available countries
     * Returns array of objects with 'id' and 'name' keys
     *
     * @param int $languageId
     *
     * @return array [['id' => '', 'name' => 'Worldwide'], ['id' => 'FRA', 'name' => 'France'], ...]
     */
    public function getAvailableCountries(int $languageId): array
    {
        $result = [];
        // Add "Worldwide" option first
        $result[] = ['value' => '', 'label' => 'Worldwide'];
        // Get countries from database
        $countries = tep_db_query("SELECT c.countries_name, c.countries_iso_code_3\r\n             FROM " . TABLE_PLATFORMS_ADDRESS_BOOK . " AS pab\r\n             LEFT JOIN " . TABLE_COUNTRIES . " AS c ON (c.countries_id = pab.entry_country_id)\r\n             WHERE c.language_id = '" . (int) $languageId . "'\r\n             GROUP BY c.countries_id\r\n             ORDER BY c.countries_name ASC");
        while ($country = tep_db_fetch_array($countries)) {
            $result[] = ['value' => $country['countries_iso_code_3'], 'label' => $country['countries_name']];
        }
        return $result;
    }
    /**
     * Get selected countries for this module
     *
     * @param int $platformId
     *
     * @return array Array of country ISO codes (empty = worldwide)
     */
    public function getSelectedCountries(int $platformId): array
    {
        $modulesCountries = ModulesCountries::findOne(['platform_id' => $platformId, 'code' => $this->moduleCode]);
        if (!$modulesCountries || empty($modulesCountries->countries)) {
            return [];
        }
        return explode(',', $modulesCountries->countries);
    }
    /**
     * Save selected countries
     *
     * @param int $platformId
     * @param array $countryCodes Array of country ISO codes (empty or [''] = worldwide)
     *
     * @return bool
     *
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function saveSelectedCountries(int $platformId, array $countryCodes): bool
    {
        $modulesCountries = ModulesCountries::findOne(['platform_id' => $platformId, 'code' => $this->moduleCode]);
        // If worldwide (empty array or array with empty string), delete restriction
        if (empty($countryCodes) || count($countryCodes) === 1 && $countryCodes[0] === '') {
            if (!is_null($modulesCountries)) {
                return $modulesCountries->delete() !== \false;
            }
            return \true;
        }
        // Create or update restriction
        if (is_null($modulesCountries)) {
            $modulesCountries = new ModulesCountries();
            $modulesCountries->platform_id = $platformId;
            $modulesCountries->code = $this->moduleCode;
        }
        $modulesCountries->countries = implode(',', $countryCodes);
        return $modulesCountries->save(\false);
    }
    /**
     * Get all available areas/contexts
     * Returns array of objects with 'id' and 'name' keys
     *
     * @return array [['id' => 'admin', 'name' => 'Admin area'], ['id' => 'shop_order', 'name' => 'Checkout'], ...]
     */
    public function getAvailableAreas(): array
    {
        $areas = ['admin' => 'Admin area', 'shop_order' => 'Checkout', 'pos' => 'POS', 'shop_quote' => 'Quote', 'shop_sample' => 'Sample'];
        $result = [];
        foreach ($areas as $id => $name) {
            if (!method_exists(Extensions::class, 'isVisibilityVariant') || Extensions::isVisibilityVariant($name)) {
                $result[] = ['value' => $id, 'label' => $name];
            }
        }
        return $result;
    }
    /**
     * Get selected areas for this module
     *
     * @param int $platformId
     * @return array Array of selected area keys
     */
    public function getSelectedAreas(int $platformId): array
    {
        $modulesVisibility = ModulesVisibility::findOne(['platform_id' => $platformId, 'code' => $this->moduleCode]);
        if (!$modulesVisibility || empty($modulesVisibility->area)) {
            return [];
        }
        return explode(',', $modulesVisibility->area);
    }
    /**
     * Save selected areas
     *
     * @param int $platformId
     * @param array $areas Array of area keys
     * @return bool
     */
    public function saveSelectedAreas(int $platformId, array $areas): bool
    {
        $modulesVisibility = ModulesVisibility::findOne(['platform_id' => $platformId, 'code' => $this->moduleCode]);
        if (!is_object($modulesVisibility)) {
            $modulesVisibility = new ModulesVisibility();
            $modulesVisibility->platform_id = $platformId;
            $modulesVisibility->code = $this->moduleCode;
        }
        $modulesVisibility->area = implode(',', $areas);
        return $modulesVisibility->save(\false);
    }
    /**
     * Get all available options for all restriction types
     *
     * @param int $languageId
     * @return array
     */
    public function getAllAvailableOptions(int $languageId): array
    {
        return ['allowedOnlyFor' => $this->getAvailableGroups(), 'availableForCountries' => $this->getAvailableCountries($languageId), 'availableFor' => $this->getAvailableAreas()];
    }
    /**
     * Get all selected options for all restriction types
     *
     * @param int $platformId
     * @return array
     */
    public function getAllSelectedOptions(int $platformId): array
    {
        return ['allowedOnlyFor' => $this->getSelectedGroups($platformId), 'availableForCountries' => $this->getSelectedCountries($platformId), 'availableFor' => $this->getSelectedAreas($platformId)];
    }
    /**
     * Save all selected options at once
     *
     * @param int $platformId
     * @param array $data ['allowedOnlyFor' => [...], 'availableForCountries' => [...], 'availableFor' => [...]]
     *
     * @return array ['success' => bool, 'errors' => array]
     *
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function saveAllSelectedOptions(int $platformId, array $data): array
    {
        $errors = [];
        $success = \true;
        // Save group restrictions
        if (isset($data['allowedOnlyFor'])) {
            if (!$this->saveSelectedGroups($platformId, $data['allowedOnlyFor'])) {
                $errors[] = 'Failed to save group restrictions';
                $success = \false;
            }
        }
        // Save country restrictions
        if (isset($data['availableForCountries'])) {
            if (!$this->saveSelectedCountries($platformId, $data['availableForCountries'])) {
                $errors[] = 'Failed to save country restrictions';
                $success = \false;
            }
        }
        // Save area restrictions
        if (isset($data['availableFor'])) {
            if (!$this->saveSelectedAreas($platformId, $data['availableFor'])) {
                $errors[] = 'Failed to save area restrictions';
                $success = \false;
            }
        }
        return ['success' => $success, 'errors' => $errors];
    }
    /**
     * Reset all restrictions for a platform (used on disconnect)
     *
     * @param int $platformId
     *
     * @return bool
     *
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function resetAllRestrictions(int $platformId): bool
    {
        $groupsReset = $this->saveSelectedGroups($platformId, []);
        $countriesReset = $this->saveSelectedCountries($platformId, []);
        $areasReset = $this->saveSelectedAreas($platformId, []);
        return $groupsReset && $countriesReset && $areasReset;
    }
}
