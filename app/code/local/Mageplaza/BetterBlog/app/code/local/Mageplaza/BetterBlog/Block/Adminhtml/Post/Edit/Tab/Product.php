<?php
/**
 * Mageplaza_BetterBlog extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Mageplaza
 * @package        Mageplaza_BetterBlog
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * post - tag relation edit block
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterBlog
 * @author      Sam
 */
class Mageplaza_BetterBlog_Block_Adminhtml_Post_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     *
     * @access protected
     * @author Sam
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('product_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        if ($this->getPost()->getId()) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    /**
     * prepare the tag collection
     *
     * @access protected
     * @return Mageplaza_BetterBlog_Block_Adminhtml_Post_Edit_Tab_Product
     * @author Sam
     */
    protected function _prepareCollection()
    {
        //$collection = Mage::getResourceModel('catalog/product_collection');
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku');
            //->addAttributeToSelect('price');
        if ($this->getPost()->getId()) {
            $constraint = 'related.post_id='.$this->getPost()->getId();
        } else {
            $constraint = 'related.post_id=0';
        }
        $collection->getSelect()->joinLeft(
            array('related' => $collection->getTable('mageplaza_betterblog/post_product')),
            'related.entity_id=e.entity_id AND '.$constraint,
            array('position')
        );
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * prepare mass action grid
     *
     * @access protected
     * @return Mageplaza_BetterBlog_Block_Adminhtml_Post_Edit_Tab_Product
     * @author Sam
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * prepare the grid columns
     *
     * @access protected
     * @return Mageplaza_BetterBlog_Block_Adminhtml_Post_Edit_Tab_Product
     * @author Sam
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            array(
                 'header_css_class' => 'a-center',
                 'type'             => 'checkbox',
                 'name'             => 'in_products',
                 'values'           => $this->_getSelectedProducts(),
                 'align'            => 'center',
                 'index'            => 'entity_id',
            )
        );
        $this->addColumn(
            'id',
            array(
                 'header'   => Mage::helper('catalog')->__('ID'),
                 'sortable' => true,
                 'width'    => '60px',
                 'index'    => 'entity_id',
            )
        );
        $this->addColumn(
            'name',
            array(
                 'header' => Mage::helper('catalog')->__('Name'),
                 'index'  => 'name',
            )
        );
        $this->addColumn(
            'sku',
            array(
                 'header' => Mage::helper('catalog')->__('SKU'),
                 'width'  => '120px',
                 'index'  => 'sku',
            )
        );
        $this->addColumn(
            'position',
            array(
                 'header'         => Mage::helper('catalog')->__('Position'),
                 'name'           => 'position',
                 'type'           => 'number',
                 'validate_class' => 'validate-number',
                 'index'          => 'position',
                 'width'          => '60px',
                 'editable'       => true,
            )   
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve selected 
     *
     * @access protected
     * @return array
     * @author Sam
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getPostProducts();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedProducts());
        }
        return $products;
    }

    /**
     * Retrieve selected {{siblingsLabels}}
     *
     * @access protected
     * @return array
     * @author Sam
     */
    public function getSelectedProducts()
    {
        $products = array();
        $selected = Mage::registry('current_post')->getSelectedProducts();
        if (!is_array($selected)) {
            $selected = array();
        }
        foreach ($selected as $product) {
            $products[$product->getId()] = array('position' => $product->getPosition());
        }
        return $products;
    }

    /**
     * get row url
     *
     * @access public
     * @param Mageplaza_BetterBlog_Model_Tag
     * @return string
     * @author Sam
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * get grid url
     *
     * @access public
     * @return string
     * @author Sam
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/productsGrid',
            array(
                'id' => $this->getPost()->getId()
            )
        );
    }

    /**
     * get the current post
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Post
     * @author Sam
     */
    public function getPost()
    {
        return Mage::registry('current_post');
    }

    /**
     * Add filter
     *
     * @access protected
     * @param object $column
     * @return Mageplaza_BetterBlog_Block_Adminhtml_Post_Edit_Tab_Product
     * @author Sam
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
