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
use Cgi\RecommendedProducts\Model\ResourceModel\Recommended\CollectionFactory;
use Cgi\RecommendedProducts\Service\Logger\RecommendedProductLogger;
use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class DeleteRecommendedProducts
 * @package Cgi\RecommendedProducts\Cron
 */
class DeleteRecommendedProducts
{
    /**
     * Logger
     *
     * @var RecommendedProductLogger
     */
    protected $productLogger;

    /**
     * Date
     *
     * @var DateTime
     */
    protected $dateTime;

    /**
     * SliderCollection
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * DeleteRecommendedProducts constructor.
     *
     * @param RecommendedProductLogger $productLogger     Logger
     * @param CollectionFactory        $collectionFactory SliderCollection
     * @param DateTime                 $dateTime          Date
     */
    public function __construct(
        RecommendedProductLogger $productLogger,
        CollectionFactory $collectionFactory,
        DateTime $dateTime
    ) {
        $this->productLogger = $productLogger;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Delete Expired Products
     *
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
        } catch (Exception $e) {
            $this->productLogger->critical($e->getMessage());
        }
        return $this;
    }
}
