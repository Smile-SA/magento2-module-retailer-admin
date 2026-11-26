<?php

declare(strict_types=1);

namespace Smile\RetailerAdmin\Rewrite\User\Block\User\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Locale\OptionInterface;
use Magento\Framework\Registry;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Api\RetailerRepositoryInterface;

class Main extends \Magento\User\Block\User\Edit\Tab\Main
{
    /**
     * Main constructor.
     *
     * @param \Magento\Backend\Block\Template\Context  $context               Context
     * @param \Magento\Framework\Registry              $registry              Registry
     * @param \Magento\Framework\Data\FormFactory      $formFactory           Html Form factory
     * @param \Magento\Backend\Model\Auth\Session      $authSession           Admin session
     * @param \Magento\Framework\Locale\ListsInterface $localeLists           List of locales
     * @param RetailerRepositoryInterface              $retailerRepository    The retailer repository
     * @param SearchCriteriaBuilderFactory             $searchCriteriaBuilder The criteria builder
     * @param array                                    $data                  Additional Data
     * @param OptionInterface|null                     $deployedLocales       List of installed locales
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Session $authSession,
        ListsInterface $localeLists,
        protected RetailerRepositoryInterface $retailerRepository,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilder,
        array $data = [],
        ?OptionInterface $deployedLocales = null
    ) {
        parent::__construct($context, $registry, $formFactory, $authSession, $localeLists, $data, $deployedLocales);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) - Magento property name.
     */
    protected function _prepareForm(): Form
    {
        parent::_prepareForm();

        $form = $this->getForm();

        /** @var \Magento\User\Model\User $model */
        $model = $this->_coreRegistry->registry('permissions_user');

        $extra = $model->getData('extra') ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true);
        }

        $fieldset = $form->addFieldset('retailer', ['legend' => __('Retailer Information')]);
        $fieldset->addField(
            'allowed_retailer',
            'multiselect',
            [
                'name' => 'extra[retailers]',
                'values' => $this->getRetailersListToOptionArray(),
                'label' => 'Allowed retailers',
                'value' => $extra['retailers'] ?? [],
            ]
        );

        return $this;
    }

    /**
     * Returns retailers list of options.
     */
    protected function getRetailersListToOptionArray(): array
    {
        $searchResults = $this->retailerRepository->getList($this->searchCriteriaBuilder->create()->create());

        return array_map(function (RetailerInterface $retailer) {
            return [
                'value' => $retailer->getId(),
                'label' => $retailer->getName(),
            ];
        }, $searchResults->getItems());
    }
}
