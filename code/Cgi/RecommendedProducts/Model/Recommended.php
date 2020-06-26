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
use Cgi\RecommendedProducts\Api\Data\RecommendedInterfaceFactory;
use Cgi\RecommendedProducts\Model\ResourceModel\Recommended\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Recommended
 *
 * @package Cgi\RecommendedProducts\Model
 */
class Recommended extends AbstractModel
{
    /**
     * @var RecommendedInterfaceFactory
     */
    protected $recommendedDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Recommended Constructor
     *
     * @param Context                     $context                Context for parent
     * @param Registry                    $registry               Registry
     * @param RecommendedInterfaceFactory $recommendedDataFactory Recommended Factory
     * @param DataObjectHelper            $dataObjectHelper       Data Object Helper
     * @param ResourceModel\Recommended   $resource               Resource Model
     * @param Collection                  $resourceCollection     Collection
     * @param array                       $data                   data array
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RecommendedInterfaceFactory $recommendedDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\Recommended $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->recommendedDataFactory = $recommendedDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve recommended model with recommended data
     *
     * @return RecommendedInterface
     */
    public function getDataModel()
    {
        $recommendedData = $this->getData();
        $recommendedDataObject = $this->recommendedDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $recommendedDataObject,
            $recommendedData,
            RecommendedInterface::class
        );
        return $recommendedDataObject;
    }
}
