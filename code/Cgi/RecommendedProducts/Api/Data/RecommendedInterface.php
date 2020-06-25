<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Api\Data;

/**
 * Interface RecommendedInterface
 * @package Cgi\RecommendedProducts\Api\Data
 */
interface RecommendedInterface
{
    /**
     * Id
     */
    public const ID = 'id';

    /**
     * Entity Id
     */
    public const ENTITY_ID = 'entity_id';

    /**
     * Type
     */
    public const TYPE = 'type';

    /**
     * Customer Id
     */
    public const CUSTOMER_ID = 'customer_id';

    /**
     * Product Id
     */
    public const PRODUCT_ID = 'product_id';

    /**
     * Product Sku
     */
    public const PRODUCT_SKU = 'product_sku';

    /**
     * Product Name
     */
    public const PRODUCT_NAME = 'product_name';

    /**
     * Created Time
     */
    public const CREATED_AT = 'created_at';

    /**
     * Updated Time
     */
    public const PRODUCT_UPDATED_AT = 'product_updated_at';

    /**
     * Priority
     */
    public const PRIORITY = 'priority';

    /**
     * Get ID
     * @return int
     */
    public function getId();

    /**
     * Get entity_id
     * @return int
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param int $entityId
     * @return RecommendedInterface
     */
    public function setEntityId($entityId);

    /**
     * Set id
     * @param int $id
     * @return RecommendedInterface
     */
    public function setId($id);

    /**
     * Get type
     * @return string
     */
    public function getType();

    /**
     * Set type
     * @param string $type
     * @return RecommendedInterface
     */
    public function setType($type);

    /**
     * Get customer_id
     * @return string
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return RecommendedInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get product_id
     * @return string
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return RecommendedInterface
     */
    public function setProductId($productId);

    /**
     * Get product_sku
     * @return string
     */
    public function getProductSku();

    /**
     * Set product_sku
     * @param string $productSku
     * @return RecommendedInterface
     */
    public function setProductSku($productSku);

    /**
     * Get product_name
     * @return string
     */
    public function getProductName();

    /**
     * Set product_name
     * @param string $productName
     * @return RecommendedInterface
     */
    public function setProductName($productName);

    /**
     * Get created_at
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return RecommendedInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get product_updated_at
     * @return string
     */
    public function getProductUpdatedAt();

    /**
     * Set product_updated_at
     * @param string $productUpdatedAt
     * @return RecommendedInterface
     */
    public function setProductUpdatedAt($productUpdatedAt);

    /**
     * Get priority
     * @return string
     */
    public function getPriority();

    /**
     * Set priority
     * @param string $priority
     * @return RecommendedInterface
     */
    public function setPriority($priority);
}
