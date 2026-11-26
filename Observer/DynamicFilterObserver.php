<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Smile\RetailerAdmin\Helper\FilterHelper;

class DynamicFilterObserver implements ObserverInterface
{
    /**
     * DynamicFilterObserver constructor.
     *
     * @param FilterHelper $filterHelper Filter helper.
     */
    public function __construct(protected FilterHelper $filterHelper)
    {
    }

    /**
     * Observer of the event "core_collection_abstract_load_before".
     *
     * @param Observer $observer The event observer.
     * @return void
     *
     * {@event core_collection_abstract_load_before; @area: adminhtml}
     * @throws \Zend_Db_Select_Exception
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $observer->getData('collection');

        $tables = $collection->getSelect()->getPart('from');
        $resource = $collection->getConnection();
        $retailerTableName = $this->filterHelper->getSellerTable();

        foreach ($tables as $alias => $tableData) {
            $name = $tableData['tableName'];
            $foreignKeys = $resource->getForeignKeys($name);

            foreach ($foreignKeys as $foreignKey) {
                if ($foreignKey['REF_TABLE_NAME'] === $retailerTableName) {
                    $this->filterHelper->applyFilterOnCollection(
                        $collection,
                        $alias,
                        $foreignKey['COLUMN_NAME']
                    );
                }
            }

            if ($name === $retailerTableName) {
                $this->filterHelper->applyFilterOnCollection($collection, $alias);
            }
        }
    }
}
