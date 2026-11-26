<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\DataObject;
use Smile\Retailer\Api\RetailerRepositoryInterface;

class Retailers extends AbstractRenderer
{
    protected SearchCriteriaBuilderFactory $criteriaBuilderFactory;

    /**
     * Retailers constructor.
     *
     * @param Context                      $context            Context.
     * @param RetailerRepositoryInterface  $retailerRepository Retailer repository.
     * @param SearchCriteriaBuilderFactory $criteriaBuilder    Criteria builder.
     * @param array                        $data               Data.
     */
    public function __construct(
        Context $context,
        protected RetailerRepositoryInterface $retailerRepository,
        SearchCriteriaBuilderFactory $criteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->criteriaBuilderFactory = $criteriaBuilder;
    }

    /**
     * Returns formatted data.
     *
     * @param \Magento\Framework\DataObject $row Row.
     */
    public function render(DataObject $row): string
    {
        $extra = $row->getExtra() ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true);
        }

        if (!$extra || !isset($extra['retailers'])) {
            return '';
        }

        $criteria = $this->criteriaBuilderFactory->create();
        $criteria->addFilter('entity_id', $extra['retailers'], 'IN');
        $retailers = $this->retailerRepository->getList($criteria->create())->getItems();

        $renderer = [];
        foreach ($retailers as $retailer) {
            $renderer[] = $retailer->getName();
        }

        return implode('|', $renderer);
    }
}
