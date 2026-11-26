<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Observer;

use Exception;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Smile\RetailerAdmin\Api\AdminRetailersInterface;
use Smile\RetailerAdmin\Helper\FilterHelper;

use function in_array;

class DynamicModelFilterObserver implements ObserverInterface
{
    /**
     * DynamicFilterObserver constructor.
     *
     * @param FilterHelper $filterHelper Filter helper.
     * @param Registry     $registry     Registry.
     */
    public function __construct(protected FilterHelper $filterHelper, protected Registry $registry)
    {
    }

    /**
     * Observer of the event "model_load_after".
     *
     * @param Observer $observer The event observer.
     * @return void
     *
     * {@event model_load_after; @area: adminhtml}
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function execute(Observer $observer): void
    {
        if ($this->registry->registry(AdminRetailersInterface::PREVENT_FILTER_FLAG)) {
            return;
        }

        /** @var \Magento\Framework\Model\AbstractModel $object */
        $object = $observer->getData('object');

        try {
            /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|AbstractEntity $resourceModel */
            $resourceModel = $object->getResource();
        } catch (Exception $e) {
            return;
        }

        if ($resourceModel instanceof AbstractEntity) {
            $table = $resourceModel->getEntityTable();
        } else {
            $table = $resourceModel->getMainTable();
        }

        $resource = $resourceModel->getConnection();
        $retailerTableName = $this->filterHelper->getSellerTable();

        $adminRetailerIds = $this->filterHelper->getFilterSellerIds(false);

        if (count($adminRetailerIds) === 0) {
            return;
        }

        $foreignKeys = $resource->getForeignKeys($table);

        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey['REF_TABLE_NAME'] === $retailerTableName) {
                $retailerId = $object->getData($foreignKey['COLUMN_NAME']);
                if (!in_array($retailerId, $adminRetailerIds, false)) {
                    $object->setData([]);
                    throw new NoSuchEntityException();
                }
            }
        }
    }
}
