<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Cron;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Api\RecommendedRepositoryInterface;
use Cgi\RecommendedProducts\Model\ResourceModel\Recommended\CollectionFactory;
use Cgi\RecommendedProducts\Service\Logger\RecommendedProductLogger;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DeleteRecommendedProducts
{
    /**
     * @var RecommendedProductLogger
     */
    protected $productLogger;

    /**
     * @var RecommendedRepositoryInterface
     */
    protected $recommendedRepository;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * DeleteRecommendedProducts constructor.
     *
     * @param RecommendedProductLogger       $productLogger
     * @param RecommendedRepositoryInterface $recommendedRepository
     * @param CollectionFactory              $collectionFactory
     * @param DateTime                       $dateTime
     */
    public function __construct(
        RecommendedProductLogger $productLogger,
        RecommendedRepositoryInterface $recommendedRepository,
        CollectionFactory $collectionFactory,
        DateTime $dateTime
    ) {
        $this->productLogger = $productLogger;
        $this->recommendedRepository = $recommendedRepository;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        try {
            $currentData = $this->dateTime->gmtDate(); // current date
            $condition = strtotime('-30 day', strtotime($currentData));
            $expiredDate = date('Y-m-d h:i:s', $condition);
            $expiredProduct = $this->collectionFactory->create()
                ->addFieldToFilter(RecommendedInterface::PRODUCT_UPDATED_AT, ['lt' => $expiredDate]);
            if ($expiredProduct) {
                $expiredProduct->walk('delete');
                $message = __("Expired Products Deleted Successfully.");
                $this->productLogger->info($message);
            }
        } catch (\Exception $e) {
            $this->productLogger->critical($e->getMessage());
        }
        return $this;
    }
}
