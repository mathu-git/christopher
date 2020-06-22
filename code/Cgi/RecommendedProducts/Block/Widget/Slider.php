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
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
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
     * Slider constructor.
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
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
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
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
                ->order(RecommendedInterface::UPDATED_AT, SortOrder::SORT_DESC)
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
}
