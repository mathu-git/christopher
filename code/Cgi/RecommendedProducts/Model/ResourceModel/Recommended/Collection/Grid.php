<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Cgi\RecommendedProducts\Model\ResourceModel\Recommended\Collection;

use Cgi\RecommendedProducts\Api\Data\RecommendedInterface;
use Cgi\RecommendedProducts\Model\ResourceModel\Recommended\Collection;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Psr\Log\LoggerInterface;

class Grid extends Collection
{
    /**
     * @var Http
     */
    private $request;

    /**
     * Grid constructor.
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        Http $request,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null
    ) {
        $this->request = $request;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection
        );
    }

    /**
     * @return Grid|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $customerId = $this->request->getParam('id');
        $this->addFieldToFilter(RecommendedInterface::CUSTOMER_ID, $customerId);
        $this->setOrder(RecommendedInterface::PRIORITY, SortOrder::SORT_ASC);
        $this->setOrder(RecommendedInterface::UPDATED_AT);
    }
}
