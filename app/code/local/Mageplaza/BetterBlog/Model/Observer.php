<?php

class Mageplaza_BetterBlog_Model_Observer
{
    /**
     * Add blog posts and categories to the standard Magento/OpenMage sitemap.
     * Listens to: sitemap_urlset_generating_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function addBlogToSitemap($observer)
    {
        $io      = $observer->getEvent()->getFile();
        $storeId = $observer->getEvent()->getStoreId();
        $date    = $observer->getEvent()->getDate();

        /**
         * Blog categories
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('mageplaza_betterblog/category_collection')
            ->addStoreFilter(Mage::app()->getStore($storeId))
            ->addFieldToFilter('status', 1);

        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($this->_filterUrl($item->getCategoryUrl())),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Blog posts
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('mageplaza_betterblog/post_collection')
            ->setStoreId($storeId)
            ->addAttributeToSelect('url_key')
            ->addAttributeToFilter('status', 1)
            ->setOrder('created_at', 'desc');

        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($this->_filterUrl($item->getPostUrl())),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
    }

    /**
     * Cron: generate the standalone blog sitemap (blog.xml)
     */
    public function scheduledGenerateSitemaps()
    {
        Mage::getModel('mageplaza_betterblog/generateSitemap')->generateXml();
    }

    /**
     * Strip index.php/ from URLs
     *
     * @param string $url
     * @return string
     */
    protected function _filterUrl($url)
    {
        return str_replace('index.php/', '', $url);
    }
}
