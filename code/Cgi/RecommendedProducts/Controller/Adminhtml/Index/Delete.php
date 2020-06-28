<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Controller\Adminhtml\Index;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Api\RecommendedRepositoryInterface;
use Cgi\RecommendedProducts\Service\Logger\RecommendedProductLogger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 *
 * @package Cgi\RecommendedProducts\Controller\Adminhtml\Index
 */
class Delete extends Action
{
    /**
     * @var RecommendedRepositoryInterface
     */
    protected $recommendedRepo;

    /**
     * @var RecommendedProductLogger
     */
    protected $recommendedLog;

    /**
     * @var Context
     */
    private $context;

    /**
     * Delete constructor.
     *
     * @param Context                        $context   Context for parent
     * @param RecommendedRepositoryInterface $recommendedRepo   RecommendedRepositoryInterface
     * @param RecommendedProductLogger       $recommendedLog    RecommendedProductLogger
     */
    public function __construct(
        Context $context,
        RecommendedRepositoryInterface $recommendedRepo,
        RecommendedProductLogger $recommendedLog
    ) {
        $this->recommendedRepo = $recommendedRepo;
        $this->recommendedLog = $recommendedLog;
        parent::__construct($context);
        $this->context = $context;
    }

    /**
     * Delete Slider Product
     *
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam(RecommendedInterface::ID);
        /**
         * Check Customer Id is Exist
        */
        if (isset($customerId)) {
            try {
                $this->recommendedRepo->deleteById($customerId);
            } catch (LocalizedException $e) {
                $this->recommendedLog->critical($e->getMessage());
            }
        }
        return $this;
    }
}
