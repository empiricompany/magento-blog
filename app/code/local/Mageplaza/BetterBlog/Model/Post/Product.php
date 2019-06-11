<?php
class Mageplaza_BetterBlog_Model_Post_Product extends Mage_Core_Model_Abstract
{   
    protected function _construct()
    {
        $this->_init('mageplaza_betterblog/post_product');
    }

    /**
     * Save data for post - product relation
     * @access public
     * @param  Mageplaza_BetterBlog_Model_Post $post
     * @return Mageplaza_BetterBlog_Model_Post_Product
     * @author Sam
     */
    public function savePostRelation($post)
    {
        //return $this;
        $data = $post->getProductsData();
        //Mage::log(__METHOD__.' products data');
        //Mage::log($data);
        if (!is_null($data)) {
            $this->_getResource()->savePostRelation($post, $data);
        }
        return $this;
    }

    /**
     * get  for post
     *
     * @access public
     * @param Mageplaza_BetterBlog_Model_Post $post
     * @return Mageplaza_BetterBlog_Model_Resource_Post_Tag_Collection
     * @author Sam
     */
    public function getProductsCollection($post)
    {
        Mage::log(__METHOD__);
        $collection = Mage::getResourceModel('mageplaza_betterblog/post_product_collection')
            ->addPostFilter($post);
        return $collection;
    }
}