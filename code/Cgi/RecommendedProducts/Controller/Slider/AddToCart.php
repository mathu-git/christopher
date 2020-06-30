<?php
/**
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cgi\RecommendedProducts\Controller\Slider;

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class AddToCart
 *
 * @package Cgi\RecommendedProducts\Controller\Slider
 */
class AddToCart extends Action
{
    /**
     * Product Qty
     */
    public const PRODUCT_QTY = 1;

    /**
     * FormKey
     *
     * @var FormKey
     */
    protected $formKey;

    /**
     * Cart Items
     *
     * @var Cart
     */
    protected $cart;

    /**
     * MessageManager
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * ProductRepository
     *
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * AddToCart constructor.
     *
     * @param Context           $context           Context for parent
     * @param FormKey           $formKey           FormKey
     * @param ProductRepository $productRepository ProductRepository
     * @param Cart              $cart              CartItems
     * @param ManagerInterface  $messageManager    MessageManager
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        ProductRepository $productRepository,
        Cart $cart,
        ManagerInterface $messageManager
    ) {
        $this->formKey = $formKey;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Ajax Add to Cart
     *
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $params = [
            'form_key' => $this->formKey->getFormKey(),
            'product' => $productId, //product Id
            'qty' =>  self::PRODUCT_QTY //quantity of product
        ];
        $product = $this->productRepository->getById($productId);
        /* @var ProductRepository $product */
        try {
            $this->cart->addProduct($product, $params);
            $this->cart->save();
            $message = __("You added %1 to your shopping cart.", $product->getName());
            $this->messageManager->addSuccessMessage($message);
        } catch (Exception $e) {
            $message = __("We don't have as many %1 as you requested.", $product->getName());
            $this->messageManager->addErrorMessage($message);
        }
    }
}
