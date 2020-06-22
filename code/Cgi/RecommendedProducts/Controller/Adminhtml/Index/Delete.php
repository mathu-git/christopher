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
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 * @package Cgi\RecommendedProducts\Controller\Adminhtml\Index
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var RecommendedRepositoryInterface
     */
    private $recommendedRepositoryInterface;

    /**
     * @var RecommendedProductLogger
     */
    private $recommendedProductLogger;

    /**
     * Delete constructor.
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param RecommendedRepositoryInterface $recommendedRepositoryInterface
     * @param RecommendedProductLogger $recommendedProductLogger
     * @param RedirectInterface $redirect
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        RecommendedRepositoryInterface $recommendedRepositoryInterface,
        RecommendedProductLogger $recommendedProductLogger,
        RedirectInterface $redirect
    ) {
        $this->redirect = $redirect;
        $this->recommendedRepositoryInterface = $recommendedRepositoryInterface;
        $this->recommendedProductLogger = $recommendedProductLogger;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam(RecommendedInterface::ID);
        /** Check Customer Id is Exist */
        if (isset($customerId)) {
            try {
                $this->recommendedRepositoryInterface->deleteById($customerId);
            } catch (LocalizedException $e) {
                $this->recommendedProductLogger->critical($e->getMessage());
            }
        }
        return $this;
    }
}
