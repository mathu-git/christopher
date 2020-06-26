<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Service;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Api\Data\RecommendedInterfaceFactory;
use Cgi\RecommendedProducts\Api\RecommendedRepositoryInterface;
use Magento\Framework\Exception\LocalizedException as LocalizedExceptionAlias;

/**
 * Class SaveResult
 *
 * @package Cgi\RecommendedProducts\Service
 */
class SaveResult
{
    /**
     * @var RecommendedInterfaceFactory
     */
    protected $recommendedInterfaceFactory;

    /**
     * @var RecommendedRepositoryInterface
     */
    protected $recommendedRepositoryInterface;

    /**
     * SaveResult constructor.
     *
     * @param RecommendedInterfaceFactory    $recommendedInterfaceFactory
     * @param RecommendedRepositoryInterface $recommendedRepositoryInterface
     */
    public function __construct(
        RecommendedInterfaceFactory $recommendedInterfaceFactory,
        RecommendedRepositoryInterface $recommendedRepositoryInterface
    ) {
        $this->recommendedInterfaceFactory = $recommendedInterfaceFactory;
        $this->recommendedRepositoryInterface = $recommendedRepositoryInterface;
    }

    /**
     * @param  int    $priority   Priority
     * @param  int    $productId  ProductId
     * @param  string $name       Product Name
     * @param  string $sku        Sku
     * @param  string $type       Type
     * @param  string $customerId CustomerId
     * @param  int    $date       TimeStamp
     * @return RecommendedInterface
     * @throws LocalizedExceptionAlias
     */
    public function saveSearchResult($priority, $productId, $name, $sku, $type, $customerId, $date)
    {
        $recommended = $this->recommendedInterfaceFactory->create();
        $recommended->setPriority($priority)
            ->setProductId($productId)
            ->setProductName($name)
            ->setProductSku($sku)
            ->setType($type)
            ->setCustomerId($customerId)
            ->setCreatedAt($date);
        return $this->recommendedRepositoryInterface->save($recommended);
    }

    /**
     * @param  array  $orderProductList OrderProducts
     * @param  int    $priority         Priority
     * @param  string $type             Type
     * @param  int    $cId              CustomerId
     * @param  int    $date             Date
     * @return RecommendedInterface
     * @throws LocalizedExceptionAlias
     */
    public function saveProducts($orderProductList, $priority, $type, $cId, $date)
    {
        foreach ($orderProductList->getItems() as $productExistItem) {
            $recommended = $this->recommendedRepositoryInterface
                ->getById($productExistItem->getId());
            /**
             * check the priority and the customer is same or not
             */
            if (($productExistItem->getPriority() > $priority)
                && ($productExistItem->getCustomerId() == $cId)
            ) {
                $recommended->setPriority($priority)
                    ->setType($type)
                    ->setCustomerId($cId)
                    ->setProductUpdatedAt($date);
            } elseif ($productExistItem->getCustomerId() == $cId) {
                $recommended->setProductUpdatedAt($date);
            }
            return $this->recommendedRepositoryInterface->save($recommended);
        }
    }
}
