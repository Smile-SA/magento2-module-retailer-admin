<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Plugin\Framework\Data\Collection;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Registry;
use Smile\RetailerAdmin\Api\AdminRetailersInterface;
use Smile\RetailerAdmin\Helper\FilterHelper;

class AbstractDbPlugin
{
    /**
     * AbstractDbPlugin constructor.
     *
     * @param FilterHelper $filterHelper Filter helper.
     * @param Registry     $registry     Registry.
     */
    public function __construct(protected FilterHelper $filterHelper, protected Registry $registry)
    {
    }

    /**
     * Add "on the fly" a filter on any tables that have a seller based on allowed retailer of the current admin user.
     *
     * @param AbstractDb $subject The plugin subject.
     * @throws \Zend_Db_Select_Exception
     */
    public function beforeGetSelectCountSql(AbstractDb $subject): array
    {
        if ($this->registry->registry(AdminRetailersInterface::PREVENT_FILTER_FLAG)) {
            return [];
        }

        $tables = $subject->getSelect()->getPart('from');
        $resource = $subject->getConnection();
        $retailerTableName = $this->filterHelper->getSellerTable();

        $adminRetailerIds = $this->filterHelper->getFilterSellerIds(false);

        if (count($adminRetailerIds) === 0) {
            return [];
        }

        foreach ($tables as $alias => $tableData) {
            $name = $tableData['tableName'];
            $foreignKeys = $resource->getForeignKeys($name);

            foreach ($foreignKeys as $foreignKey) {
                if ($foreignKey['REF_TABLE_NAME'] === $retailerTableName) {
                    $subject->getSelect()->where(
                        $alias . '.' . $foreignKey['COLUMN_NAME'] . ' in (?)',
                        $adminRetailerIds
                    );
                }
            }
        }

        return [];
    }
}
