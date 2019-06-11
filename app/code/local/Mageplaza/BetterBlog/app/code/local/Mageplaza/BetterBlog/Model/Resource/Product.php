<?php
class Mageplaza_BetterBlog_Model_Resource_Product extends Mage_Catalog_Model_Resource_Abstract
{
    protected function _construct()
    {
        $this->_init('mageplaza_betterblog/post_product','post_id');
    }
}