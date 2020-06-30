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
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Block\Product\Viewed;
use Magento\Search\Model\QueryFactory;

/**
 * Class SearchResultInfo
 *
 * @package Cgi\RecommendedProducts\Observer
 */
class SearchResultInfo implements ObserverInterface
{
    /**
     * Order Type order
     */
    public const SEARCH_TYPE = 'search';

    /**
     * Recommended Product Priority
     */
    public const PRIORITY = '2';

    /**
     * Product Name
     */
    public const NAME = 'name';

    /**
     * RecommendedRepositoryInterface
     *
     * @var RecommendedRepositoryInterface
     */
    protected $recommendedRepo;

    /**
     * Date
     *
     * @var DateTime
     */
    protected $date;

    /**
     * Recently Viewed Product
     *
     * @var Viewed
     */
    protected $recentlyViewed;

    /**
     * ProductRepository
     *
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * SearchCriteriaBuilder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * Query
     *
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * CustomerSessionFactory
     *
     * @var CustomerSessionFactory
     */
    protected $customerSession;

    /**
     * Service
     *
     * @var SaveResult
     */
    protected $saveResult;

    /**
     * Logger
     *
     * @var RecommendedProductLogger
     */
    protected $logger;

    /**
     * FilterBuilder
     *
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * SaveRecommendedInfo constructor.
     *
     * @param RecommendedRepositoryInterface $recommendedRepo   Recommended Repository Interface
     * @param SessionFactory                 $sessionFactory    CustomerSessionFactory
     * @param FilterBuilder                  $filterBuilder     FilterBuilder
     * @param RecommendedProductLogger       $logger            Logger
     * @param SearchCriteriaBuilder          $searchCriteria    SearchCriteriaBuilder
     * @param ProductRepository              $productRepository ProductRepository
     * @param Viewed                         $recentlyViewed    Recently Viewed Product
     * @param SaveResult                     $saveResult        Service
     * @param QueryFactory                   $queryFactory      Query
     * @param DateTime                       $date              DateTime
     */
    public function __construct(
        RecommendedRepositoryInterface $recommendedRepo,
        SessionFactory $sessionFactory,
        FilterBuilder $filterBuilder,
        RecommendedProductLogger $logger,
        SearchCriteriaBuilder $searchCriteria,
        ProductRepository $productRepository,
        Viewed $recentlyViewed,
        SaveResult $saveResult,
        QueryFactory $queryFactory,
        DateTime $date
    ) {
        $this->recommendedRepo = $recommendedRepo;
        $this->customerSession = $sessionFactory;
        $this->logger = $logger;
        $this->recentlyViewed = $recentlyViewed;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->queryFactory = $queryFactory;
        $this->saveResult = $saveResult;
        $this->filterBuilder = $filterBuilder;
        $this->date = $date;
    }

    /**
     * Execute Observer When Search
     *
     * @param  Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /**
         * Check the customer is logged in
         */
        $customer = $this->customerSession->create();
        if ($customer->isLoggedIn()) {
            $searchTerm = $this->queryFactory->get()->getQueryText();
            $searchCriteria = $this->searchCriteria
                ->addFilter(self::NAME, $searchTerm, 'eq')->create();
            $product = $this->productRepository->getList($searchCriteria);
            $customerId = $customer->getCustomerId();
            $date = $this->date->gmtDate();
            /**
             * Check the product exist in magento
             */
            if ($product->getTotalCount()) {
                try {
                    $filter1 = [];
                    foreach ($product->getItems() as $searchItem) {
                        $filter1[] = $this->filterBuilder
                            ->setField(RecommendedInterface::PRODUCT_ID)
                            ->setConditionType('like')
                            ->setValue($searchItem->getId())
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
                    $productExist = $this->recommendedRepo->getList($searchCriteria);
                    /**
                     * Check the product exist in custom table and save the product
                     */
                    if ($productExist->getTotalCount()) {
                        $this->saveResult->saveProducts(
                            $productExist,
                            self::PRIORITY,
                            self::SEARCH_TYPE,
                            $customerId,
                            $date
                        );
                    } else {
                        /**
                         * Save new product to custom table
                         */
                        foreach ($product->getItems() as $searchItem) {
                            $this->saveResult->saveSearchResult(
                                self::PRIORITY,
                                $searchItem->getId(),
                                $searchItem->getName(),
                                $searchItem->getSku(),
                                self::SEARCH_TYPE,
                                $customerId,
                                $date
                            );
                        }
                    }
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
            return $this;
        }
    }
}
