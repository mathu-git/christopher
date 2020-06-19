<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Api;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Api\Data\RecommendedSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface RecommendedRepositoryInterface
 * @package Cgi\RecommendedProducts\Api
 */
interface RecommendedRepositoryInterface
{
    /**
     * Retrieve RecommendedInterface
     * @param string $entityId
     * @return RecommendedInterface
     * @throws LocalizedException
     */
    public function getById($entityId);

    /**
     * Retrieve Source matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return RecommendedSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Save Recommended
     * @param RecommendedInterface $recommended
     * @return RecommendedInterface
     * @throws LocalizedException
     */
    public function save(RecommendedInterface $recommended);

    /**
     * Delete Recommended
     * @param RecommendedInterface $recommended
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RecommendedInterface $recommended);

    /**
     * Delete Recommended by ID
     * @param string $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($entityId);
}
