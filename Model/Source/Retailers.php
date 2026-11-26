<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Smile\Retailer\Api\RetailerRepositoryInterface;

class Retailers extends AbstractSource
{
    /**
     * Retailers constructor.
     *
     * @param RetailerRepositoryInterface  $retailerRepository    Retailer Repository.
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilder Search criteria builder.
     */
    public function __construct(
        protected RetailerRepositoryInterface $retailerRepository,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilder
    ) {
    }

    /**
     * Returns all retailers for option list.
     */
    public function getAllOptions(): array
    {
        $options = [
            [
                'value' => '',
                'label' => ' ',
            ],
        ];
        $searchResults = $this->retailerRepository->getList($this->searchCriteriaBuilder->create()->create());

        foreach ($searchResults->getItems() as $retailer) {
            $options[] = [
                'value' => $retailer->getId(),
                'label' => $retailer->getName(),
            ];
        }

        return $options;
    }
}
