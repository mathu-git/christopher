<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Model;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Api\Data\RecommendedSearchResultsInterface;
use Cgi\RecommendedProducts\Api\Data\RecommendedSearchResultsInterfaceFactory;
use Cgi\RecommendedProducts\Api\RecommendedRepositoryInterface;
use Cgi\RecommendedProducts\Model\ResourceModel\Recommended as ResourceRecommended;
use Cgi\RecommendedProducts\Model\ResourceModel\Recommended\CollectionFactory as RecommendedCollectionFactory;
use Exception;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class RecommendedProductsRepository
 *
 * @package Cgi\RecommendedProducts\Model
 */
class RecommendedRepository implements RecommendedRepositoryInterface
{
    /**
     * Search Results InterfaceFactory
     *
     * @var RecommendedSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Collection Processor Interface
     *
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * Resource Recommended
     *
     * @var ResourceRecommended
     */
    protected $resource;

    /**
     * Recommended Collection Factory
     *
     * @var RecommendedCollectionFactory
     */
    protected $recommendedCollectionFactory;

    /**
     * Recommended Factory
     *
     * @var RecommendedFactory
     */
    protected $recommendedFactory;

    /**
     * Extensible DataObject Converter
     *
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObj;

    /**
     * RecommendedProductsRepository constructor.
     *
     * @param ResourceRecommended                      $resource                     Resource Recommended
     * @param RecommendedFactory                       $recommendedFactory           Recommended Factory
     * @param RecommendedCollectionFactory             $recommendedCollectionFactory Recommended Collection Factory
     * @param RecommendedSearchResultsInterfaceFactory $searchResultsFactory         Search Results InterfaceFactory
     * @param CollectionProcessorInterface             $collectionProcessor          Collection Processor Interface
     * @param ExtensibleDataObjectConverter            $extensibleDataObj            Extensible DataObject Converter
     */
    public function __construct(
        ResourceRecommended $resource,
        RecommendedFactory $recommendedFactory,
        RecommendedCollectionFactory $recommendedCollectionFactory,
        RecommendedSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        ExtensibleDataObjectConverter $extensibleDataObj
    ) {
        $this->resource = $resource;
        $this->recommendedFactory = $recommendedFactory;
        $this->recommendedCollectionFactory = $recommendedCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensibleDataObj = $extensibleDataObj;
    }

    /**
     * Save Data
     *
     * @param RecommendedInterface $recommended
     * @return RecommendedInterface
     * {@inheritdoc}
     */
    public function save(RecommendedInterface $recommended)
    {
        $recommendedData = $this->extensibleDataObj->toNestedArray(
            $recommended,
            [],
            RecommendedInterface::class
        );

        try {
            $recommendedModel = $this->recommendedFactory->create();
            $recommendedModel->setData($recommendedData);
            $recommendedModel->save();
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the recommended: %1',
                    $exception->getMessage()
                )
            );
        }
        return $recommendedModel->getDataModel();
    }

    /**
     * Get by Id
     *
     * @param string $entityId
     * @return RecommendedInterface
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        $recommended = $this->recommendedFactory->create();
        $this->resource->load($recommended, $entityId);
        if (!$recommended->getId()) {
            throw new NoSuchEntityException(__('Recommended item with id "%1" does not exist.', $entityId));
        }
        return $recommended->getDataModel();
    }

    /**
     * Get List of Items
     *
     * @param SearchCriteriaInterface $criteria
     * @return RecommendedSearchResultsInterface
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->recommendedCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete
     *
     * @param RecommendedInterface $recommended
     * {@inheritdoc}
     */
    public function delete(RecommendedInterface $recommended)
    {
        try {
            $recommendedModel = $this->recommendedFactory->create();
            $this->resource->load($recommendedModel, $recommended->getEntityId());
            $this->resource->delete($recommendedModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the Recommended: %1',
                    $exception->getMessage()
                )
            );
        }
        return true;
    }

    /**
     * DeleteBYId
     *
     * @param string $entityId
     * {@inheritdoc}
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }
}
