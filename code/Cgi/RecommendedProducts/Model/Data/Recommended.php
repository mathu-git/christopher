<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Model\Data;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Litter
 * @package Cgi\RecommendedProducts\Model\Data
 */
class Recommended extends AbstractExtensibleObject implements RecommendedInterface
{
    /**
     * Get ID
     * @return int
     */
    public function getId()
    {
        return $this->getEntityId();
    }

    /**
     * Get entity_id
     * @return int
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param int $entityId
     * @return RecommendedInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Set id
     * @param int $id
     * @return RecommendedInterface
     */
    public function setId($id)
    {
        return $this->setEntityId($id);
    }

    /**
     * Get recommended_type
     * @return string
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * Set recommended_type
     * @param string $type
     * @return RecommendedInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get customer_id
     * @return string
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return RecommendedInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get product_id
     * @return string
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param string $productId
     * @return RecommendedInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get product_name
     * @return string
     */
    public function getProductName()
    {
        return $this->_get(self::PRODUCT_NAME);
    }

    /**
     * Set product_name
     * @param string $productName
     * @return RecommendedInterface
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * Get product_sku
     * @return string
     */
    public function getProductSku()
    {
        return $this->_get(self::PRODUCT_SKU);
    }

    /**
     * Set product_sku
     * @param string $productSku
     * @return RecommendedInterface
     */
    public function setProductSku($productSku)
    {
        return $this->setData(self::PRODUCT_SKU, $productSku);
    }

    /**
     * Get created_at
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return RecommendedInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return RecommendedInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get priority
     * @return string
     */
    public function getPriority()
    {
        return $this->_get(self::PRIORITY);
    }

    /**
     * Set priority
     * @param string $priority
     * @return RecommendedInterface
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }
}
