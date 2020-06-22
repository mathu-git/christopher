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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;

/**
 * Class Index
 * @package Cgi\RecommendedProducts\Controller\Adminhtml\Index
 */
class Index extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        return $this->resultLayoutFactory->create();
    }
}