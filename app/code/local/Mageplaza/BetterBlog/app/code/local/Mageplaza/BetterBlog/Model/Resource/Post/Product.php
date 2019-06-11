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
 * Post - Product relation model
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterBlog
 * @author      Sam
 */
class Mageplaza_BetterBlog_Model_Resource_Post_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * initialize resource model
     *
     * @access protected
     * @return void
     * @see Mage_Core_Model_Resource_Abstract::_construct()
     * @author Sam
     */
    protected function  _construct()
    {
        $this->_init('mageplaza_betterblog/post_product', 'post_id');
    }

    /**
     * Save post - product relations
     *
     * @access public
     * @param Mageplaza_BetterBlog_Model_Post $post
     * @param array $data
     * @return Mageplaza_BetterBlog_Model_Resource_Post_Product
     * @author Sam
     */
    public function savePostRelation($post, $data)
    {
        if (!is_array($data)) {
            $data = array();
        }
        $adapter = $this->_getWriteAdapter();
        $bind    = array(
            ':post_id'    => (int)$post->getId(),
        );
        $select = $adapter->select()
            ->from($this->getMainTable(), array('entity_id'))
            ->where('post_id = :post_id');

        $related   = $adapter->fetchCol($select, $bind);
        $deleteIds = array();
        foreach ($related as $relId => $productId) {
            if (!isset($data[$productId])) {
                $adapter->delete(
                    $this->getMainTable(),
                    'entity_id = '. (int)$productId .' AND post_id = '.(int)$post->getId()
                );
                //$deleteIds[] = (int)$productId;
            }
        }
        /*if (!empty($deleteIds)) {
            $adapter->delete(
                $this->getMainTable(),
                array('entity_id ' => $deleteIds, 'post_id' => (int)$post->getId())
            );
        }*/

        foreach ($data as $productId => $info) {
            $adapter->insertOnDuplicate(
                $this->getMainTable(),
                array(
                    'post_id'      => $post->getId(),
                    'entity_id'     => $productId,
                    'position'      => @$info['position']
                ),
                array('position')
            );
        }
        return $this;
    }
}
