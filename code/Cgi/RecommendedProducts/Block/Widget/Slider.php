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
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Slider
 *
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
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Slider constructor.
     *
     * @param Session $customerSession Customer Session
     * @param CollectionFactory $collectionFactory  Product Collection
     * @param Context $context  Context for parent
     * @param PostHelper $postDataHelper Helper Data
     * @param Resolver $layerResolver Resolver
     * @param FormKey $formKey Form Key
     * @param Cart $cart Cart Items
     * @param CategoryRepositoryInterface $categoryRepository Category Repository
     * @param Data $urlHelper Url Helper
     */
    public function __construct(
        Session $customerSession,
        CollectionFactory $collectionFactory,
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        FormKey $formKey,
        Cart $cart,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper
    ) {
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->cart = $cart;
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
     * Slider Product Collection
     *
     * @return array
     */
    public function getProductCollection()
    {
        $customer = $this->getCustomerLoggedIn();
        $collection = [];
        if ($customer->isLoggedIn()) {
            $customerId = $customer->getCustomerId();
            $cartInfo = $this->cart->getQuote()->getAllItems();
            $productIds= [];
            foreach ($cartInfo as $product) {
                $productIds[] = $product->getProductId();
            }
            $joinConditions = 'u.product_id = e.entity_id';
            $collection = $this->collectionFactory->create();
            $collection = $collection->addAttributeToSelect('*');
            if ($productIds) {
                $collection->addAttributeToFilter('entity_id', array('nin' => $productIds));
            }
            $collection->getSelect()->join(
                ['u' => $collection->getTable(Recommended::TABLE_NAME)],
                $joinConditions,
                []
            )->where('u.customer_id = ?', $customerId)
                ->order(RecommendedInterface::PRIORITY)
                ->order('u.product_updated_at desc')
                ->limit(self::PRODUCT_COUNT);
        }
        /**
         * Check the customer is Logged In
        */
        return $collection;
    }

    /**
     * Customer Session
     *
     * @return Session
     */
    public function getCustomerLoggedIn()
    {
        return $this->customerSession;
    }

    /**
     * Store BaseUrl
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
    }

    /**
     * Form Key
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
