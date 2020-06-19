<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Recommended
 * @package Cgi\RecommendedProducts\Model\ResourceModel
 */
class Recommended extends AbstractDb
{
    /**
     * Initialize resource
     * @return void
     */
    public function _construct()
    {
        $this->_init('recommended_products', 'entity_id');
    }
}
