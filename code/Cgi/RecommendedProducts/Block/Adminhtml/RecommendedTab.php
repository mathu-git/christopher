<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Block\Adminhtml;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

/**
 * Class RecommendedTab
 * @package Cgi\RecommendedProducts\Block\Adminhtml
 */
class RecommendedTab extends TabWrapper implements TabInterface
{
    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * @var Http
     */
    private $request;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Http $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        Http $request,
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|mixed|null
     */
    public function canShowTab()
    {
        return $this->request->getParam(RecommendedInterface::ID);
    }

    /**
     * Return Tab label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Recommended Products');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('recommended/*/index', ['_current' => true]);
    }
}
