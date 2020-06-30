<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Plugin\Magento\Catalog\Controller\Product;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Api\RecommendedRepositoryInterface;
use Cgi\RecommendedProducts\Service\Logger\RecommendedProductLogger;
use Cgi\RecommendedProducts\Service\SaveResult;
use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Block\Product\Viewed;

/**
 * Class View
 *
 * @package Cgi\RecommendedProducts\Plugin\Magento\Catalog\Controller\Product
 */
class View
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
     * RecommendedRepositoryInterface
     *
     * @var RecommendedRepositoryInterface
     */
    protected $recommendedRepo;

    /**
     * DateTime
     *
     * @var DateTime
     */
    protected $date;

    /**
     * CustomerSession
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Viewed
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
     * SaveRecommendedInfo constructor.
     *
     * @param RecommendedRepositoryInterface $recommendedRepo   Recommended Repository Interface
     * @param Session                        $customerSession   CustomerSession
     * @param SearchCriteriaBuilder          $searchCriteria    SearchCriteriaBuilder
     * @param ProductRepository              $productRepository ProductRepository
     * @param SaveResult                     $saveResult        Service
     * @param RecommendedProductLogger       $logger            Logger
     * @param Viewed                         $recentlyViewed    Viewed
     * @param DateTime                       $date              DateTime
     */
    public function __construct(
        RecommendedRepositoryInterface $recommendedRepo,
        Session $customerSession,
        SearchCriteriaBuilder $searchCriteria,
        ProductRepository $productRepository,
        SaveResult $saveResult,
        RecommendedProductLogger $logger,
        Viewed $recentlyViewed,
        DateTime $date
    ) {
        $this->recommendedRepo = $recommendedRepo;
        $this->customerSession = $customerSession;
        $this->saveResult = $saveResult;
        $this->recentlyViewed = $recentlyViewed;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->date = $date;
    }

    /**
     * Before Plugin for Viewing the product
     *
     * @param  \Magento\Catalog\Controller\Product\View $subject ProductView
     * @return View
     */
    public function beforeExecute(
        \Magento\Catalog\Controller\Product\View $subject
    ) {
        /**
         * Check the customer is logged in
         */
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $date = $this->date->gmtDate();
            try {
                $productId = (int)$subject->getRequest()->getParam(RecommendedInterface::ID);
                $productRepository = $this->productRepository->getById($productId);
                $productName = $productRepository->getName();
                $productSku = $productRepository->getSku();
                $searchCriteria = $this->searchCriteria
                    ->addFilter(RecommendedInterface::PRODUCT_ID, $productId, 'eq')
                    ->addFilter(RecommendedInterface::CUSTOMER_ID, $customerId, 'eq')
                    ->create();
                $viewProductList = $this->recommendedRepo->getList($searchCriteria);
                /**
                 * Check the product exist in custom table
                 */
                if ($viewProductList->getTotalCount()) {
                    /**
                     * Viewed Product Items
                     */
                    foreach ($viewProductList->getItems() as $productExistItem) {
                        $recommended = $this->recommendedRepo
                            ->getById($productExistItem->getId());
                        $recommended->setProductUpdatedAt($date);
                    }
                    $this->recommendedRepo->save($recommended);
                } else {
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
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $this;
    }
}
