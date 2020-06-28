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
     * @var RecommendedRepositoryInterface
     */
    protected $recommendedRepo;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Viewed
     */
    protected $recentlyViewed;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

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
    protected $logger;

    /**
     * SaveRecommendedInfo constructor.
     *
     * @param RecommendedRepositoryInterface $recommendedRepo Recommended Repository Interface
     * @param Session                        $customerSession
     * @param SearchCriteriaBuilder          $searchCriteria
     * @param ProductRepository              $productRepository
     * @param SaveResult                     $saveResult
     * @param RecommendedProductLogger       $logger
     * @param Viewed                         $recentlyViewed
     * @param DateTime                       $date                           DateTime
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
     * @param \Magento\Catalog\Controller\Product\View $subject
     * @return View
     */
    public function beforeExecute(
        \Magento\Catalog\Controller\Product\View $subject
    ) {
        /**
         * check the customer is logged in
         */
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        if ($this->customerSession->isLoggedIn()) {
            $logger->info(print_r('1', true));
            $customerId = $this->customerSession->getCustomerId();
            $date = $this->date->gmtDate();
            try {
                $logger->info(print_r('2', true));
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
                 * check the product exist in custom table
                 */
                if ($viewProductList->getTotalCount()) {
                    $logger->info(print_r('3', true));
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
