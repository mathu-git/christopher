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

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Recommended
 * @package Cgi\RecommendedProducts\Model\ResourceModel
 */
class Recommended extends AbstractDb
{
    /**
     * Table Name
     */
    public const TABLE_NAME = 'recommended_products';

    /**
     * Initialize resource
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::TABLE_NAME, RecommendedInterface::ENTITY_ID);
    }
}
