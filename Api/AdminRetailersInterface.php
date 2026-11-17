<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Api;

use Smile\Retailer\Api\Data\RetailerInterface;

/**
 * Interface AdminRetailersInterface
 */
interface AdminRetailersInterface
{
    public const PREVENT_FILTER_FLAG = 'smile_admin_retailer_prevent_filter';

    /**
     * Get all Retailer Ids associated to the current Admin user
     *
     * @param bool $defaultToAll if true, no value mean all retailer
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getAdminRetailerIds(bool $defaultToAll = true);

    /**
     * Get the retailer of the current Admin
     *
     * @param bool $defaultToAll If no retailers is found on the admin user, all retailer is returned
     * @return RetailerInterface[]
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getAdminRetailers(bool $defaultToAll = true);

    /**
     * Set Retailers.
     *
     * @param array $retailers Retailers.
     * @return void
     */
    public function setRetailers(array $retailers);
}
