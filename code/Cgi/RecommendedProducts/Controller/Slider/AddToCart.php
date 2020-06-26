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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class AddToCart
 *
 * @package Cgi\RecommendedProducts\Controller\Slider
 */
class AddToCart extends Action
{
    /**
     * @var UrlInterface
     */
    private $_urlInterface;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * AddToCart constructor.
     *
     * @param Context $context
     * @param UrlInterface $urlInterface
     * @param FormKey $formKey
     * @param ProductRepository $productRepository
     * @param Cart $cart
     * @param ManagerInterface $messageManager
     * @param Product $product
     */
    public function __construct(
        Context $context,
        UrlInterface $urlInterface,
        FormKey $formKey,
        ProductRepository $productRepository,
        Cart $cart,
        ManagerInterface $messageManager,
        Product $product
    ) {
        $this->_urlInterface = $urlInterface;
        $this->formKey = $formKey;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->_messageManager = $messageManager;
        $this->product = $product;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $params = [
            'form_key' => $this->formKey->getFormKey(),
            'product' => $productId, //product Id
            'qty' => 1 //quantity of product
        ];
        $_product = $this->productRepository->getById($productId);
        $url = $this->_urlInterface->getUrl('checkout/cart', ['_secure' => true]);
        try {
            $this->cart->addProduct($_product, $params);
            $this->cart->save();
            $message = __("You added %1 to your shopping cart.", $_product->getName());
            $this->messageManager->addSuccess($message);
        } catch (\Exception $e) {
            $message = __("We don't have as many %1 as you requested.", $_product->getName());
            $this->messageManager->addErrorMessage($message);
        }
    }
}
