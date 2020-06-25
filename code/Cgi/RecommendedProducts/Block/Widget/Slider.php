<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Block\Widget;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Model\ResourceModel\Recommended;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Slider
 * @package Cgi\RecommendedProducts\Block\Widget
 */
class Slider extends ListProduct implements BlockInterface
{
    /**
     * Template File
     */
    protected $_template = "widget/slider.phtml";

    /**
     * Product Count
     */
    public const PRODUCT_COUNT = 15;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * Slider constructor.
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param FormKey $formKey
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param array $data
     */
    public function __construct(
        Session $customerSession,
        CollectionFactory $collectionFactory,
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        FormKey $formKey,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->collectionFactory = $collectionFactory;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper
        );
    }

    /**
     * @return array
     */
    public function getProductCollection()
    {
        $customer = $this->getCustomerLoggedIn();
        $collection = [];
        /** Check the customer is Logged In */
        if ($customer->isLoggedIn()) {
            $customerId = $customer->getCustomerId();
            $joinConditions = 'u.product_id = e.entity_id';
            $collection = $this->collectionFactory->create();
            $collection = $collection->addAttributeToSelect('*');
            $collection->getSelect()->join(
                ['u' => $collection->getTable(Recommended::TABLE_NAME)],
                $joinConditions,
                []
            )->where('u.customer_id = ?', $customerId)
                ->order(RecommendedInterface::PRIORITY, SortOrder::SORT_ASC)
                ->order('u.product_updated_at desc')
                ->limit(self::PRODUCT_COUNT);
        }
        return $collection;
    }

    /**
     * @return Session
     */
    public function getCustomerLoggedIn()
    {
        return $this->customerSession;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
