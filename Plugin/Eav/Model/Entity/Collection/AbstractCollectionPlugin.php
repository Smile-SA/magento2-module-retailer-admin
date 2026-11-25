<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Plugin\Eav\Model\Entity\Collection;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Smile\RetailerAdmin\Api\AdminRetailersInterface;
use Smile\RetailerAdmin\Helper\FilterHelper;

class AbstractCollectionPlugin
{
    /**
     * AbstractCollectionPlugin constructor.
     *
     * @param FilterHelper $filterHelper Filter helper.
     * @param Registry     $registry     Registry.
     */
    public function __construct(protected FilterHelper $filterHelper, protected Registry $registry)
    {
    }

    /**
     * Add "on the fly" a filter on seller tables based on allowed retailer of the current admin user.
     *
     * @param AbstractCollection $subject    The plugin subject.
     * @param mixed              $printQuery 1rst params of `load` function
     * @param mixed              $logQuery   2nd params of `load` function
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @throws \Exception
     */
    public function beforeLoad(AbstractCollection $subject, $printQuery = false, $logQuery = false): array
    {
        if ($this->registry->registry(AdminRetailersInterface::PREVENT_FILTER_FLAG)) {
            return [$printQuery, $logQuery];
        }

        $retailerTableName = $this->filterHelper->getSellerTable();

        if ($subject->getMainTable() === $retailerTableName) {
            $this->filterHelper->applyFilterOnCollection($subject);
        }

        return [$printQuery, $logQuery];
    }
}
