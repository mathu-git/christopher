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
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Block\Product\Viewed;
use Magento\Search\Model\QueryFactory;

/**
 * Class SearchResultInfo
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
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;

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
     * SaveRecommendedInfo constructor.
     * @param RecommendedInterfaceFactory $recommendedInterfaceFactory Recommended Interface Factory
     * @param RecommendedRepositoryInterface $recommendedRepositoryInterface Recommended Repository Interface
     * @param Session $customerSession
     * @param SessionFactory $sessionFactory
     * @param FilterBuilder $filterBuilder
     * @param RecommendedProductLogger $recommendedProductLogger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepository $productRepository
     * @param Viewed $recentlyViewed
     * @param SaveResult $saveResult
     * @param QueryFactory $queryFactory
     * @param DateTime $date DateTime
     */
    public function __construct(
        RecommendedInterfaceFactory $recommendedInterfaceFactory,
        RecommendedRepositoryInterface $recommendedRepositoryInterface,
        Session $customerSession,
        SessionFactory $sessionFactory,
        FilterBuilder $filterBuilder,
        RecommendedProductLogger $recommendedProductLogger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepository $productRepository,
        Viewed $recentlyViewed,
        SaveResult $saveResult,
        QueryFactory $queryFactory,
        DateTime $date
    ) {
        $this->recommendedInterfaceFactory = $recommendedInterfaceFactory;
        $this->recommendedRepositoryInterface = $recommendedRepositoryInterface;
        $this->customerSessionFactory = $sessionFactory;
        $this->customerSession = $customerSession;
        $this->recommendedProductLogger = $recommendedProductLogger;
        $this->recentlyViewed = $recentlyViewed;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->queryFactory = $queryFactory;
        $this->saveResult = $saveResult;
        $this->filterBuilder = $filterBuilder;
        $this->date = $date;
    }

    /**
     * Execute Observer
     * @param Observer $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** check the customer is logged in */
        $customer = $this->customerSessionFactory->create();
        if ($customer->isLoggedIn()) {
            $searchTerm = $this->queryFactory->get()->getQueryText();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('name', $searchTerm, 'eq')->create();
            $product = $this->productRepository->getList($searchCriteria);
            $customerId = $customer->getCustomerId();
            $date = $this->date->gmtDate();
            /** check the product exist in magento */
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
                    $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilters($filter1)
                        ->addFilters($filter2)
                        ->create();
                    $productExist = $this->recommendedRepositoryInterface->getList($searchCriteria);
                    /** check the product exist in custom table and save the product */
                    if ($productExist->getTotalCount()) {
                        $this->saveResult->saveProducts(
                            $productExist,
                            self::PRIORITY,
                            self::SEARCH_TYPE,
                            $customerId,
                            $date
                        );
                    } else {
                        /** Save new product to custom table */
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
                } catch (\Exception $e) {
                    $this->recommendedProductLogger->critical($e->getMessage());
                }
            }
            return $this;
        }
    }
}
