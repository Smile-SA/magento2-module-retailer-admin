<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Plugin\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentInterface;
use Smile\RetailerAdmin\Helper\FilterHelper;

class ContextPlugin
{
    /**
     * ContextPlugin constructor.
     *
     * @param FilterHelper $filterHelper Filter helper.
     */
    public function __construct(protected FilterHelper $filterHelper)
    {
    }

    /**
     * Check role before add button.
     *
     * @param Context              $subject   Subject.
     * @param array                $buttons   Buttons.
     * @param UiComponentInterface $component Component.
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function beforeAddButtons(Context $subject, array $buttons, UiComponentInterface $component): array
    {
        if (
            isset($buttons['add'])
            && isset($buttons['add']['id'])
            && $buttons['add']['id'] == 'add-new-retailer'
        ) {
            $adminRetailerIds = $this->filterHelper->getFilterSellerIds(false);
            if (count($adminRetailerIds) !== 0) {
                unset($buttons['add']);
            }
        }

        return [$buttons, $component];
    }
}
