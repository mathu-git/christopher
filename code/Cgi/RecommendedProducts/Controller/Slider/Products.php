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

use Cgi\RecommendedProducts\Block\Widget\Slider;
use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Helper\Output;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Products
 *
 * @package Cgi\RecommendedProducts\Controller\Slider
 */
class Products extends Action
{
    /**
     * @var Slider
     */
    private $slider;

    /**
     * @var Output
     */
    private $helper;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Products constructor.
     *
     * @param Context     $context
     * @param Slider      $slider
     * @param Output      $_helper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Slider $slider,
        Output $_helper,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->slider = $slider;
        $this->helper = $_helper;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        return $this->products($this->slider->getProductCollection());
    }

    /**
     * @param  $collection
     * @throws LocalizedException
     */
    public function products($collection)
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()
            ->createBlock('Cgi\RecommendedProducts\Block\Widget\Slider')
            ->setTemplate('Cgi_RecommendedProducts::widget/slider.phtml')
            ->toHtml();
        $image = 'category_page_grid';
        $templateType = ReviewRendererInterface::SHORT_VIEW;
        $showCart = true;
        $showReview = true;
        $product_count = count($collection);
        if ($product_count < 5) :
            $class = "lessproduct"; else:
                $class = "";
            endif;
            $html = '';
            $html .= '<div class="cgi-bestsaletabs mc- ' . $class . '">';
            if (!$collection) :
                $html .= '<div class="message info empty">';
                $html .= "<div><span>We can't find products matching the selection.</span>";
                $html .= '</div></div>'; else:
                    $html .= '<div class="products wrapper grid products-grid">';
                    $html .= '<div class="product_count" value="' . $product_count . '">' . $product_count . '</div>';
                    $iterator = 1;
                    $html .= '<ol class="products list items product-items owlslider">';
                    foreach ($collection as $_product):
                        $html .= $iterator++ == 1 ? '<li class="item product product-item">' : '</li>
<li class="item product product-item">';
                        $html .= '<div class="product-item-info" data-container="product-grid">';
                        $productImage = $this->slider->getImage($_product, $image);
                        $html .= '<div class="images-container">';
                        $html .= '<a href="' . $_product->getProductUrl() . '" 
                        class="product photo product-item-photo" tabindex="-1">' . $productImage->toHtml();
                        $html .= '</a>';
                        $html .= '<div class="actions-no hover-box">';
                        $html .= '<a class="detail_links" href="' . $_product->getProductUrl() . '">';
                        $html .= '</a>';
                        $html .= '<div class="product actions product-item-actions">';
                        $html .= '<strong class=product-item-name>';
                        $html .= '<a class="product-item-link" title="' . $_product->getName() . '"
             href="' . $_product->getProductUrl() . '">' . $this->helper
                            ->productAttribute($_product, $_product->getName(), 'name');
                        $html .= '</a></strong>';
                        if ($showCart) :
                            $html .= '<div class="actions-primary">';
                            if ($_product->isSaleable()) :
                                $html .= '<div class="product details product-item-details products-textlink">';
                                $html .= '<div class="price-review">';
                                $html .= $this->slider->getProductPrice($_product);
                                $html .= $showReview ? $this->slider
                                    ->getReviewsSummaryHtml($_product, $templateType) : '';
                                $html .= $this->slider->getProductDetailsHtml($_product);
                                $html .= '</div></div>';
                                $postParams = $this->slider->getAddToCartPostParams($_product);
                                if ($_product->getTypeId() == Configurable::TYPE_CODE 
                                    || $_product->getTypeId() == Type::TYPE_DOWNLOADABLE
                                ) {
                                    $html .= '<strong class=product-item-configurable>';
                                    $html .= '<a id="product-addtocart-button" class="action tocart primary" 
                                    title="' . $_product->getName() . '" href="' . $_product->getProductUrl() . '">
                                    Add to Cart';
                                    $html .= '</a></strong>';
                                } else {
                                    $html .= '<form data-role="tocart-form" 
                                    data-product-sku="' . $_product->getSku() . '" 
                                    action="' . $postParams['action'] . '" method="post">';
                                    $html .= '<input type="hidden" 
                                    class="product-id" name="product" 
                                    value="' . $postParams['data']['product'] . '" />';
                                    $html .= '<input type="hidden" name="' . Action::PARAM_NAME_URL_ENCODED . '" 
                                    value="' . str_replace(
                                        ",,", ",", $postParams['data']
                                        [Action::PARAM_NAME_URL_ENCODED]
                                    ) . '" />';
                                    $html .= '<input name="form_key" type="hidden" 
                                    value="' . $this->slider->getFormKey() . '" />';
                                    $html .= '<button id="product-addtocart-button" class="action tocart primary" 
                                    type="button" title="' . $this->slider->escapeHtml(__('Add to Cart')) . '" 
                                    key=' . $_product->getId() . ' 
                                    class="action tocart primary button btn-cart pull-left-none">';
                                    $html .= '<span>Add to Cart</span></button></form>';
                                } else:
                                    if ($_product->getIsSalable()) :
                                        $html .= '<div class="stock available">';
                                        $html .= '<span>In stock</span></div'; else:
                                                    $html .= '<div class="stock unavailable">';
                                            $html .= '<span>Out of stock</span></div>';
                                        endif;
                                endif;
                                $html .= '</div>';
                        endif;
                        $html .= '</div></div></div></div>';
                    endforeach;
                    $html .= '</ol></div>';
                endif;
                if (!$this->slider->isRedirectToCartEnabled()) : ?>
            <script type="text/x-magento-init">
      {
          "[data-role=tocart-form], .form.map.checkout": {
              "catalogAddToCart": {}
          }
      }
            </script>
                        <?php else : ?>
            <script type="text/x-magento-init">
   {
       "#product_addtocart_form": {
           "Magento_Catalog/js/validate-product": {}
       }
   }
            </script>
                        <?php endif;
                        $html .= '</div>';
                        echo $html;
                        exit();
    }
}
