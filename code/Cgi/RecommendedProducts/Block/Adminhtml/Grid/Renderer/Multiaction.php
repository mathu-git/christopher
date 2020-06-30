<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Action;
use Magento\Framework\DataObject as DataObjectAlias;

/**
 * Class Multi action
 *
 * @package Cgi\RecommendedProducts\Block\Adminhtml\Grid\Renderer
 */
class Multiaction extends Action
{
    /**
     * Render single action as link html
     *
     * @param  array           $action Action
     * @param  DataObjectAlias $row    Data Object
     * @return string|false
     */
    protected function _toLinkHtml(
        $action,
        DataObjectAlias $row
    ) {
        $style = '';
        $onClick = sprintf('onclick="return %s.configureItem(%s)"', 'wishlistControl', $row->getId());
        return sprintf('<a href="%s" %s %s>%s</a>', $action['url'], $style, $onClick, $action['caption']);
    }
}
