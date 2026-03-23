<?php

class Mageplaza_BetterBlog_Adminhtml_Betterblog_BlogController extends Mage_Adminhtml_Controller_Action
{
    public function generateSitemapAction()
    {
        Mage::getModel('mageplaza_betterblog/generateSitemap')->generateXml();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mageplaza_betterblog');
    }
}
