<?php

class Mageplaza_BetterBlog_Block_Sidebar extends Mage_Core_Block_Template
{
    protected $_posts = null;
    protected $_mostviews = null;
    protected $_relatedPosts = null;
    protected $_comments = null;
    protected $_categories = null;
    protected $_tags = null;

    /**
     * initialize
     *
     * @access public
     * @author Sam
     */
    public function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime' => 3600,
            'cache_tags'     => array('mageplaza_betterblog_post'),
            'cache_key'      => 'betterblog_sidebar_' . Mage::app()->getStore()->getId()
                . '_' . (Mage::registry('current_product') ? Mage::registry('current_product')->getId() : '0'),
        ));
    }

    /**
     * get recent posts collection (lazy loaded)
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Resource_Post_Collection
     */
    public function getPosts()
    {
        if ($this->_posts === null) {
            $config = Mage::helper('mageplaza_betterblog/config');
            $this->_posts = Mage::getResourceModel('mageplaza_betterblog/post_collection')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addAttributeToSelect(array('post_title', 'url_key', 'post_excerpt', 'image', 'created_at'))
                ->addAttributeToFilter('status', 1);
            $this->_posts->setOrder('created_at', 'desc');
            $count = $this->getPostCount() ? $this->getPostCount() : $config->getPostConfig('number_recent_posts');
            $this->_posts->setPageSize($count);
        }
        return $this->_posts;
    }

    /**
     * get most viewed posts collection (lazy loaded)
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Resource_Post_Collection|null
     */
    public function getMostviews()
    {
        if ($this->_mostviews === null) {
            $config = Mage::helper('mageplaza_betterblog/config');
            if ($config->getSidebarConfig('enable_mostview')) {
                $this->_mostviews = Mage::getResourceModel('mageplaza_betterblog/post_collection')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->addAttributeToSelect(array('post_title', 'url_key', 'post_excerpt', 'image', 'views'))
                    ->addAttributeToFilter('status', 1);
                $this->_mostviews->setOrder('views', 'desc');
                $count = $config->getSidebarConfig('number_mostview_posts') ? $config->getSidebarConfig('number_mostview_posts') : 5;
                $this->_mostviews->setPageSize($count);
            }
        }
        return $this->_mostviews;
    }

    /**
     * get related posts collection (lazy loaded)
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Resource_Product_Post_Collection|null
     */
    public function getRelatedPosts()
    {
        if ($this->_relatedPosts === null) {
            if (Mage::registry('current_product')) {
                $this->_relatedPosts = Mage::getResourceModel('mageplaza_betterblog/product_post_collection')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->addAttributeToSelect(array('post_title', 'url_key', 'post_excerpt', 'image', 'views'))
                    ->addAttributeToFilter('status', 1)
                    ->addProductFilter(Mage::registry('current_product'));
                $this->_relatedPosts->setOrder('views', 'desc');
            }
        }
        return $this->_relatedPosts;
    }

    /**
     * get recent comments collection (lazy loaded)
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Resource_Post_Comment_Collection|null
     */
    public function getComments()
    {
        if ($this->_comments === null) {
            if (Mage::helper('mageplaza_betterblog')->canShowCommentWidget()) {
                $config = Mage::helper('mageplaza_betterblog/config');
                $this->_comments = Mage::getResourceModel('mageplaza_betterblog/post_comment_collection')
                    ->addFieldToFilter('status', Mageplaza_BetterBlog_Model_Post_Comment::STATUS_APPROVED);
                $this->_comments->setOrder('created_at', 'desc');
                $count = $config->getSidebarConfig('number_comment') ? $config->getSidebarConfig('number_comment') : 5;
                $this->_comments->setPageSize($count);
            }
        }
        return $this->_comments;
    }

    /**
     * get categories collection (lazy loaded)
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Resource_Category_Collection
     */
    public function getCategories()
    {
        if ($this->_categories === null) {
            $this->_categories = Mage::getResourceModel('mageplaza_betterblog/category_collection')
                ->addStoreFilter(Mage::app()->getStore())
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('level', 1);
            $this->_categories->getSelect()->order('main_table.position');
        }
        return $this->_categories;
    }

    /**
     * get tags collection (lazy loaded)
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Resource_Tag_Collection
     */
    public function getTags()
    {
        if ($this->_tags === null) {
            $this->_tags = Mage::getResourceModel('mageplaza_betterblog/tag_collection')
                ->addStoreFilter(Mage::app()->getStore())
                ->addFieldToFilter('status', 1);
            $this->_tags->setOrder('name', 'asc');
        }
        return $this->_tags;
    }
}
