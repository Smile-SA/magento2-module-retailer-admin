<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Registry;
use RuntimeException;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Api\RetailerRepositoryInterface;
use Smile\RetailerAdmin\Api\AdminRetailersInterface;

use function is_string;

/**
 * Retailers allowed for the current admin user.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AdminRetailers implements AdminRetailersInterface
{
    /**
     * Instance cache for the current Admin retailer
     */
    private ?RetailerInterface $retailer = null;

    /**
     * Instance cache for the current Admin retailers
     *
     * @var RetailerInterface[]
     */
    private ?array $retailers = null;

    protected SearchCriteriaBuilderFactory $criteriaBuilderFactory;

    /**
     * Instance cache for the list of all retailers
     *
     * @var RetailerInterface[]
     */
    protected ?array $allRetailers = null;

    /**
     * AdminRetailers constructor.
     *
     * @param Session                      $authSession        Admin Session.
     * @param RetailerRepositoryInterface  $retailerRepository Retailer Repository.
     * @param Registry                     $registry           Request registry.
     * @param SearchCriteriaBuilderFactory $criteriaBuilder    Criteria Builder.
     */
    public function __construct(
        protected Session $authSession,
        protected RetailerRepositoryInterface $retailerRepository,
        protected Registry $registry,
        SearchCriteriaBuilderFactory $criteriaBuilder
    ) {
        $this->criteriaBuilderFactory = $criteriaBuilder;
    }

    /**
     * Get the retailer of the current Admin
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdminRetailer(): RetailerInterface
    {
        if (!$this->retailer) {
            $retailerIds = $this->getAuthSessionData();
            $retailerId = reset($retailerIds);

            if ($retailerId === false) {
                throw new RuntimeException(__('Retailer ID is missing')->render());
            }

            /** @var RetailerInterface $retailer */
            $retailer = $this->retailerRepository->get((int) $retailerId);
            $this->retailer = $retailer;
        }

        return $this->retailer;
    }

    /**
     * Set Retailers.
     *
     * @param array $retailers Retailers.
     */
    public function setRetailers(array $retailers): void
    {
        $this->retailers = $retailers;
    }

    /**
     * Get all Retailer Ids associated to the current Admin user
     *
     * @param bool $defaultToAll if true, no value mean all retailer
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getAdminRetailerIds(bool $defaultToAll = true): array
    {
        $retailerIds = $this->getAdminRetailers(false);

        if ($defaultToAll && count($retailerIds) === 0) {
            $retailerIds = $this->getAllRetailers();
        }

        return  array_map(function (RetailerInterface $retailer): int {
            return (int) $retailer->getId();
        }, $retailerIds);
    }

    /**
     * Get the retailer of the current Admin
     *
     * @param bool $defaultToAll If no retailers is found on the admin user, all retailer is returned
     * @return RetailerInterface[]
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getAdminRetailers(bool $defaultToAll = true): array
    {
        if (!$this->retailers) {
            $retailerIds = $this->getAuthSessionData();

            if (!$defaultToAll && count($retailerIds) === 0) {
                return [];
            }

            if (count($retailerIds) === 0) {
                return $this->getAllRetailers();
            }

            $criteria = $this->criteriaBuilderFactory->create();
            $criteria->addFilter('entity_id', $retailerIds, 'IN');

            $this->registry->register(self::PREVENT_FILTER_FLAG, true, true);
            $this->retailers = $this->retailerRepository->getList($criteria->create())->getItems();
            $this->registry->unregister(self::PREVENT_FILTER_FLAG);
        }

        return $this->retailers;
    }

    /**
     * Get the list of retailer ids set on the admin user.
     */
    protected function getAuthSessionData(): array
    {
        if (PHP_SAPI === 'cli') {
            return [];
        }
        $customerExtra = $this->authSession->getUser() ? $this->authSession->getUser()->getExtra() : [];
        if (is_string($customerExtra)) {
            $customerExtra = json_decode($customerExtra, true);
        }

        return $customerExtra['retailers'] ?? [];
    }

    /**
     * Get all retailers.
     *
     * @return RetailerInterface[]
     */
    protected function getAllRetailers(): array
    {
        if ($this->allRetailers === null) {
            $builder = $this->criteriaBuilderFactory->create();

            $this->registry->register(self::PREVENT_FILTER_FLAG, true, true);
            $this->allRetailers = $this->retailerRepository->getList($builder->create())->getItems();
            $this->registry->unregister(self::PREVENT_FILTER_FLAG);
        }

        return $this->allRetailers;
    }
}
