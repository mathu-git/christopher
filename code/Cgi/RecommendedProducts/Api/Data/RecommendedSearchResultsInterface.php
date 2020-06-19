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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface RecommendedSearchResultsInterface
 * @package Cgi\RecommendedProducts\Api\Data
 */
interface RecommendedSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Recommended list.
     * @return RecommendedInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param RecommendedInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
