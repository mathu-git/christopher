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
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class RecommendedProductsRepository
 * @package Cgi\RecommendedProducts\Model
 */
class RecommendedRepository implements RecommendedRepositoryInterface
{
    /**
     * @var RecommendedSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ResourceRecommended
     */
    private $resource;

    /**
     * @var RecommendedCollectionFactory
     */
    private $recommendedCollectionFactory;

    /**
     * @var RecommendedFactory
     */
    private $recommendedFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * RecommendedProductsRepository constructor.
     * @param ResourceRecommended $resource Resource Recommended
     * @param RecommendedFactory $recommendedFactory Recommended Factory
     * @param RecommendedCollectionFactory $recommendedCollectionFactory Recommended Collection Factory
     * @param RecommendedSearchResultsInterfaceFactory $searchResultsFactory Recommended Search Results InterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor Collection Processor Interface
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter Extensible DataObject Converter
     */
    public function __construct(
        ResourceRecommended $resource,
        RecommendedFactory $recommendedFactory,
        RecommendedCollectionFactory $recommendedCollectionFactory,
        RecommendedSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->recommendedFactory = $recommendedFactory;
        $this->recommendedCollectionFactory = $recommendedCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RecommendedInterface $recommended)
    {
        $recommendedData = $this->extensibleDataObjectConverter->toNestedArray(
            $recommended,
            [],
            RecommendedInterface::class
        );

        try {
            $recommendedModel = $this->recommendedFactory->create();
            $recommendedModel->setData($recommendedData);
            $recommendedModel->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the recommended: %1',
                $exception->getMessage()
            ));
        }
        /** @var Recommended $recommendedModel */
        return $recommendedModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        /** @var Recommended $recommended */
        $recommended = $this->recommendedFactory->create();
        $this->resource->load($recommended, $entityId);
        if (!$recommended->getId()) {
            throw new NoSuchEntityException(__('Recommended item with id "%1" does not exist.', $entityId));
        }
        return $recommended->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->recommendedCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        /** @var RecommendedSearchResultsInterface $searchResults */
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RecommendedInterface $recommended)
    {
        try {
            $recommendedModel = $this->recommendedFactory->create();
            $this->resource->load($recommendedModel, $recommended->getEntityId());
            $this->resource->delete($recommendedModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Recommended: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }
}
