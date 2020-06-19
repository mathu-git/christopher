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
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class OrderedProductInfo
 * @package Cgi\RecommendedProducts\Observer
 */
class OrderedProductInfo implements ObserverInterface
{
    /**
     * Order Type order
     */
    public const ORDER_TYPE = 'order';

    /**
     * Recommended Product Priority
     */
    public const PRIORITY = '1';

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
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * SaveRecommendedInfo constructor.
     * @param RecommendedInterfaceFactory $recommendedInterfaceFactory Recommended Interface Factory
     * @param RecommendedRepositoryInterface $recommendedRepositoryInterface Recommended Repository Interface
     * @param Session $customerSession
     * @param SaveResult $saveResult
     * @param ProductRepository $productRepository
     * @param FilterBuilder $filterBuilder
     * @param RecommendedProductLogger $recommendedProductLogger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $date DateTime
     */
    public function __construct(
        RecommendedInterfaceFactory $recommendedInterfaceFactory,
        RecommendedRepositoryInterface $recommendedRepositoryInterface,
        Session $customerSession,
        SaveResult $saveResult,
        ProductRepository $productRepository,
        FilterBuilder $filterBuilder,
        RecommendedProductLogger $recommendedProductLogger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $date
    ) {
        $this->recommendedInterfaceFactory = $recommendedInterfaceFactory;
        $this->recommendedRepositoryInterface = $recommendedRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->saveResult = $saveResult;
        $this->productRepository = $productRepository;
        $this->filterBuilder = $filterBuilder;
        $this->recommendedProductLogger = $recommendedProductLogger;
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
        /** check the customer is logged in */
        if ($this->customerSession->isLoggedIn()) {
            $order = $observer->getEvent()->getOrder();
            $customerId = $order->getCustomerId();
            $date = $this->date->gmtDate();
            try {
                $orderProductId = [];
                $filter1 = [];
                /** Ordered Items */
                foreach ($order->getAllVisibleItems() as $recommendedItem) {
                    $orderProductId[] = $recommendedItem->getProductId();
                    $filter1[] = $this->filterBuilder
                        ->setField(RecommendedInterface::PRODUCT_ID)
                        ->setConditionType('like')
                        ->setValue($recommendedItem->getProductId())
                        ->create();
                }
                $filter2[] = $this->filterBuilder
                    ->setField(RecommendedInterface::CUSTOMER_ID)
                    ->setConditionType('like')
                    ->setValue($customerId)
                    ->create();
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilters($filter1)
                    ->addFilters($filter2)
                    ->create();
                $orderProductList = $this->recommendedRepositoryInterface->getList($searchCriteria);
                $existedProductId = [];
                /** check the product exist in custom table and save the product */
                if ($orderProductList->getTotalCount()) {
                    foreach ($orderProductList->getItems() as $existedItem) {
                        $existedProductId[] = $existedItem->getProductId();
                    }
                    $this->saveResult->saveProducts(
                        $orderProductList,
                        self::PRIORITY,
                        self::ORDER_TYPE,
                        $customerId,
                        $date
                    );
                }

                /** compare and get the not existed product id */
                $newProduct = array_diff($orderProductId, $existedProductId);
                /** Save new product to custom table */
                if (!empty($newProduct)) {
                    foreach ($newProduct as $recommendedItem) {
                        $orderProduct = $this->productRepository->getById($recommendedItem);
                        $this->saveResult->saveSearchResult(
                            self::PRIORITY,
                            $recommendedItem,
                            $orderProduct->getName(),
                            $orderProduct->getSku(),
                            self::ORDER_TYPE,
                            $customerId,
                            $date
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->recommendedProductLogger->critical($e->getMessage());
            }
            return $this;
        }
    }
}
