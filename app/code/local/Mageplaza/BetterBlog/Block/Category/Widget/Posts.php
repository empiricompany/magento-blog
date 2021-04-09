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
 * Category posts widget block
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterBlog
 * @author      Tony
 */
class Mageplaza_BetterBlog_Block_Category_Widget_Posts extends Mageplaza_BetterBlog_Block_Post_List implements
    Mage_Widget_Block_Interface
{
    protected $_htmlTemplate = 'mageplaza_betterblog/category/widget/posts.phtml';


    /**
     * initialize
     *
     * @access public
     * @author Tony
     */
    public function _construct()
    {
        parent::_construct();
        $categoryId = $this->getData('category_id');
         if ($categoryId) {
             $this->getPosts()->addCategoryFilter($categoryId);
         }
         $this->getPosts()->unshiftOrder('created_at', 'desc');
         $this->setTemplate($this->_htmlTemplate);
    }
}
