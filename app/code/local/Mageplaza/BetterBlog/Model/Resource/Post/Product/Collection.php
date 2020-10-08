<?php
class Mageplaza_BetterBlog_Model_Resource_Post_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * remember if fields have been joined
     * @var bool
     */
    protected $_joinedFields = false;

    /**
     * join the link table
     *
     * @access public
     * @return Mageplaza_BetterBlog_Model_Resource_Post_Product_Collection
     * @author Sam
     */
    public function joinFields()
    {
        if (!$this->_joinedFields) {
            $this->addAttributeToSelect('*')->getSelect()->join(
                array('related' => $this->getTable('mageplaza_betterblog/post_product')),
                'related.entity_id = e.entity_id',
                array('position')
            );
            $this->_joinedFields = true;
        }
        return $this;
    }

    /**
     * add post filter
     *
     * @access public
     * @param Mageplaza_BetterBlog_Model_Post | int $post
     * @return Mageplaza_BetterBlog_Model_Resource_Post_Tag_Collection
     * @author Sam
     */
    public function addPostFilter($post)
    {
        if ($post instanceof Mageplaza_BetterBlog_Model_Post) {
            $post = $post->getId();
        }
        if (!$this->_joinedFields) {
            $this->joinFields();
        }
        $this->getSelect()->where('related.post_id = ?', $post);
        return $this;
    }
}