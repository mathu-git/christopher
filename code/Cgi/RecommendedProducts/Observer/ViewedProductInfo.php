<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Observer;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Api\Data\RecommendedInterfaceFactory;
use Cgi\RecommendedProducts\Api\RecommendedRepositoryInterface;
use Cgi\RecommendedProducts\Service\Logger\RecommendedProductLogger;
use Cgi\RecommendedProducts\Service\SaveResult;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Block\Product\Viewed;

/**
 * Class ViewedProductInfo
 * @package Cgi\RecommendedProducts\Observer
 */
class ViewedProductInfo implements ObserverInterface
{
    /**
     * Order Type order
     */
    public const VIEWED_TYPE = 'view';

    /**
     * Recommended Product Priority
     */
    public const PRIORITY = '3';

    /**
     * @var RecommendedInterfaceFactory
     */
    private $recommendedInterfaceFactory;

    /**
     * @var RecommendedRepositoryInterface
     */
    private $recommendedRepositoryInterface;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Viewed
     */
    private $recentlyViewed;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SaveResult
     */
    private $saveResult;

    /**
     * @var RecommendedProductLogger
     */
    private $recommendedProductLogger;

    /**
     * SaveRecommendedInfo constructor.
     * @param RecommendedInterfaceFactory $recommendedInterfaceFactory Recommended Interface Factory
     * @param RecommendedRepositoryInterface $recommendedRepositoryInterface Recommended Repository Interface
     * @param Session $customerSession
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepository $productRepository
     * @param SaveResult $saveResult
     * @param RecommendedProductLogger $recommendedProductLogger
     * @param Viewed $recentlyViewed
     * @param DateTime $date DateTime
     */
    public function __construct(
        RecommendedInterfaceFactory $recommendedInterfaceFactory,
        RecommendedRepositoryInterface $recommendedRepositoryInterface,
        Session $customerSession,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepository $productRepository,
        SaveResult $saveResult,
        RecommendedProductLogger $recommendedProductLogger,
        Viewed $recentlyViewed,
        DateTime $date
    ) {
        $this->recommendedInterfaceFactory = $recommendedInterfaceFactory;
        $this->recommendedRepositoryInterface = $recommendedRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->saveResult = $saveResult;
        $this->recentlyViewed = $recentlyViewed;
        $this->recommendedProductLogger = $recommendedProductLogger;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->date = $date;
    }

    /**
     * Execute Observer
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** Recently Product Collection */
        $viewedItem = $this->recentlyViewed->getItemsCollection()->load();
        /** check the customer is logged in */
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $date = $this->date->gmtDate();
            /** Viewed Product Items */
            foreach ($viewedItem->getData() as $viewedProduct) {
                try {
                    $productId = (int)$viewedProduct['entity_id'];
                    $productName = $this->productRepository->getById($productId)->getName();
                    $productSku = $viewedProduct['sku'];
                    $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter(RecommendedInterface::PRODUCT_ID, $productId, 'eq')
                    ->addFilter(RecommendedInterface::CUSTOMER_ID, $customerId, 'eq')
                    ->create();
                    $viewProductList = $this->recommendedRepositoryInterface->getList($searchCriteria);
                    /** check the product exist in custom table */
                    if ($viewProductList->getTotalCount()) {
                        foreach ($viewProductList->getItems() as $productExistItem) {
                            $recommended = $this->recommendedRepositoryInterface
                            ->getById($productExistItem->getId());
                            $recommended->setCreatedAt($date);
                        }
                        $this->recommendedRepositoryInterface->save($recommended);
                    } else {
                        /** Save new product to custom table */
                        $this->saveResult->saveSearchResult(
                            self::PRIORITY,
                            $productId,
                            $productName,
                            $productSku,
                            self::VIEWED_TYPE,
                            $customerId,
                            $date
                        );
                    }
                } catch (\Exception $e) {
                    $this->recommendedProductLogger->critical($e->getMessage());
                }
            }
            return $this;
        }
    }
}
