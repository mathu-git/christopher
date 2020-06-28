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
use Cgi\RecommendedProducts\Api\RecommendedRepositoryInterface;
use Cgi\RecommendedProducts\Service\Logger\RecommendedProductLogger;
use Cgi\RecommendedProducts\Service\SaveResult;
use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;

/**
 * Class OrderedProductInfo
 *
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
     * @var RecommendedRepositoryInterface
     */
    protected $recommendedRepo;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * @var SaveResult
     */
    protected $saveResult;

    /**
     * @var RecommendedProductLogger
     */
    protected $recommendedLogger;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * SaveRecommendedInfo constructor.
     *
     * @param RecommendedRepositoryInterface $recommendedRepo    Recommended Repository Interface
     * @param Session                        $customerSession   CustomerSession
     * @param SaveResult                     $saveResult    Service
     * @param ProductRepository              $productRepository ProductRepository
     * @param FilterBuilder                  $filterBuilder FilterBuilder
     * @param RecommendedProductLogger       $recommendedLogger  Logger
     * @param SearchCriteriaBuilder          $searchCriteria SearchCriteriaBuilder
     * @param DateTime                       $date  DateTime
     */
    public function __construct(
        RecommendedRepositoryInterface $recommendedRepo,
        Session $customerSession,
        SaveResult $saveResult,
        ProductRepository $productRepository,
        FilterBuilder $filterBuilder,
        RecommendedProductLogger $recommendedLogger,
        SearchCriteriaBuilder $searchCriteria,
        DateTime $date
    ) {
        $this->recommendedRepo = $recommendedRepo;
        $this->customerSession = $customerSession;
        $this->saveResult = $saveResult;
        $this->productRepository = $productRepository;
        $this->filterBuilder = $filterBuilder;
        $this->recommendedLogger = $recommendedLogger;
        $this->searchCriteria = $searchCriteria;
        $this->date = $date;
    }

    /**
     * Execute Observer When Order Place
     *
     * @param  Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /**
         * check the customer is logged in
         */
        if ($this->customerSession->isLoggedIn()) {
            $order = $observer->getEvent()->getOrder();
            /* @var Order $order */
            $customerId = $order->getCustomerId();
            $date = $this->date->gmtDate();
            try {
                $orderProductId = [];
                $filter1 = [];
                /**
                 * Ordered Items
                 */
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
                $searchCriteria = $this->searchCriteria
                    ->addFilters($filter1)
                    ->addFilters($filter2)
                    ->create();
                $orderProductList = $this->recommendedRepo->getList($searchCriteria);
                $existedProductId = [];
                /**
                 * check the product exist in custom table and save the product
                 */
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

                /**
                 * compare and get the not existed product id
                 */
                $newProduct = array_diff($orderProductId, $existedProductId);
                /**
                 * Save new product to custom table
                 */
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
            } catch (Exception $e) {
                $this->recommendedLogger->critical($e->getMessage());
            }
            return $this;
        }
    }
}
