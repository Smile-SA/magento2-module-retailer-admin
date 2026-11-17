<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Observer\Magento;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Rma\Model\Grid;
use Magento\Sales\Api\OrderRepositoryInterface;

class AddRetailerOnRmaGrid implements ObserverInterface
{
    /**
     * AddRetailerOnRmaGrid constructor.
     *
     * @param OrderRepositoryInterface $orderRepository The order repository
     */
    public function __construct(protected OrderRepositoryInterface $orderRepository)
    {
    }

    /**
     * Add Seller Id on RMA grid item
     *
     * @param Observer $observer The Observer.
     * @return void
     *
     * {@event core_abstract_save_before ; @area adminhtml }
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Rma\Model\Grid $rmaGrid */
        $rmaGrid = $observer->getData('data_object');

        if ((!$rmaGrid instanceof Grid)) {
            return;
        }

        $orderId = $rmaGrid->getData('order_id');

        $order = $this->orderRepository->get($orderId);

        if ($order instanceof DataObject) {
            $rmaGrid->setData('seller_id', $order->getData('seller_id'));
        }
    }
}
