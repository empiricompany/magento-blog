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
 * Post front contrller
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterBlog
 * @author      Sam
 */
class Mageplaza_BetterBlog_PostController extends Mage_Core_Controller_Front_Action
{

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Sam
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if (Mage::helper('mageplaza_betterblog/post')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label' => Mage::helper('mageplaza_betterblog')->__('Home'),
                        'link' => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'posts',
                    array(
                        'label' => Mage::helper('mageplaza_betterblog')->__('Blog'),
                        'link' => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', Mage::helper('mageplaza_betterblog/post')->getPostsUrl());
        }
        if ($headBlock) {
            $headBlock->setTitle(Mage::getStoreConfig('mageplaza_betterblog/post/meta_title'));
            $headBlock->setKeywords(Mage::getStoreConfig('mageplaza_betterblog/post/meta_keywords'));
            $headBlock->setDescription(Mage::getStoreConfig('mageplaza_betterblog/post/meta_description'));
        }
        $this->renderLayout();
    }

    /**
     * init Post
     *
     * @access protected
     * @return Mageplaza_BetterBlog_Model_Post
     * @author Sam
     */
    protected function _initPost()
    {
        $postId = (int)$this->getRequest()->getParam('id', 0);
        $post = Mage::getModel('mageplaza_betterblog/post')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($postId);
        if (!$post->getId()) {
            return false;
        } elseif (!$post->getStatus()) {
            return false;
        }
        return $post;
    }

    /**
     * view post action
     *
     * @access public
     * @return void
     * @author Sam
     */
    public function viewAction()
    {
        $post = $this->_initPost();
        if (!$post) {
            $this->_forward('no-route');
            return;
        }

        $post->updateViewCount();

        Mage::register('current_post', $post);
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('betterblog-post betterblog-post' . $post->getId());
        }
        if (Mage::helper('mageplaza_betterblog/post')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label' => Mage::helper('mageplaza_betterblog')->__('Home'),
                        'link' => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'posts',
                    array(
                        'label' => Mage::helper('mageplaza_betterblog')->__('Blog'),
                        'link' => Mage::helper('mageplaza_betterblog/post')->getPostsUrl(),
                    )
                );
                $categories = $post->getSelectedCategoriesCollection();
                foreach ($categories as $_category) {
                    $breadcrumbBlock->addCrumb(
                        'category-' . $_category->getId(),
                        array(
                            'label' => $_category->getName(),
                            'link' =>  $_category->getCategoryUrl(),
                        )
                    );
                    break;
                }

                $breadcrumbBlock->addCrumb(
                    'post',
                    array(
                        'label' => $post->getPostTitle(),
                        'link' => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', $post->getPostUrl());
        }
        if ($headBlock) {
            if ($post->getMetaTitle()) {
                $headBlock->setTitle($post->getMetaTitle());
            } else {
                $headBlock->setTitle($post->getPostTitle());
            }
            $headBlock->setKeywords($post->getMetaKeywords());
            $headBlock->setDescription($post->getMetaDescription());

            // Add JSON-LD Schema.org Article markup
            $storeId = Mage::app()->getStore()->getId();
            $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

            $imageUrl = '';
            if ($post->getImage()) {
                $imageHelper = Mage::helper('mageplaza_betterblog/post_image');
                $imageUrl = (string) $imageHelper->init($post, 'image');
                if (strpos($imageUrl, 'http') !== 0) {
                    $imageUrl = rtrim($baseUrl, '/') . '/' . ltrim($imageUrl, '/');
                }
            }

            $authorName = 'Admin';
            if ($post->getAuthorId()) {
                $customer = Mage::getModel('customer/customer')->load($post->getAuthorId());
                if ($customer && $customer->getName()) {
                    $authorName = $customer->getName();
                }
            }

            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $post->getPostTitle(),
                'description' => $post->getMetaDescription() ?: strip_tags($post->getPostExcerpt()),
                'datePublished' => date('c', strtotime($post->getCreatedAt())),
                'dateModified' => date('c', strtotime($post->getUpdatedAt() ?: $post->getCreatedAt())),
                'author' => [
                    '@type' => 'Person',
                    'name' => $authorName,
                ],
                'url' => $post->getPostUrl(),
            ];

            if ($imageUrl) {
                $schema['image'] = ['@type' => 'ImageObject', 'url' => $imageUrl];
            }

            $schema['publisher'] = [
                '@type' => 'Organization',
                'name' => Mage::app()->getStore()->getFrontendName(),
            ];

            $jsonLd = '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

            $jsonLdBlock = $this->getLayout()->createBlock('core/text')
                ->setName('mageplaza_json_ld')
                ->setText($jsonLd);
            $headBlock->append($jsonLdBlock);
        }
        $this->renderLayout();
    }

    /**
     * posts rss list action
     *
     * @access public
     * @return void
     * @author Sam
     */
    public function rssAction()
    {
        if (Mage::helper('mageplaza_betterblog/post')->isRssEnabled()) {
            $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
            $this->loadLayout(false);
            $this->renderLayout();
        } else {
            $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
            $this->getResponse()->setHeader('Status', '404 File not found');
            $this->_forward('nofeed', 'index', 'rss');
        }
    }

    /**
     * Submit new comment action
     * @access public
     * @author Sam
     */
    public function commentpostAction()
    {
        $session = Mage::getSingleton('core/session');
        if (!$this->_validateFormKey()) {
            $session->addError($this->__('Invalid form key. Please refresh the page.'));
            $this->_redirectReferer();
            return;
        }
        $data = $this->getRequest()->getPost();
        $post = $this->_initPost();
        if ($post) {
            if ($post->getAllowComments()) {
                if ((Mage::getSingleton('customer/session')->isLoggedIn() ||
                    Mage::getStoreConfigFlag('mageplaza_betterblog/post/allow_guest_comment'))
                ) {
                    $allowedFields = array('title', 'comment', 'name', 'email');
                    $filteredData = array_intersect_key($data, array_flip($allowedFields));
                    $comment = Mage::getModel('mageplaza_betterblog/post_comment')->setData($filteredData);
                    $validate = $comment->validate();
                    if ($validate === true) {
                        try {
                            $comment->setPostId($post->getId())
                                ->setStatus(Mageplaza_BetterBlog_Model_Post_Comment::STATUS_PENDING)
                                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                                ->setStores(array(Mage::app()->getStore()->getId()))
                                ->save();
                            $session->addSuccess($this->__('Your comment has been accepted for moderation.'));
                        } catch (Exception $e) {
                            $session->setPostCommentData($data);
                            $session->addError($this->__('Unable to post the comment.'));
                        }
                    } else {
                        $session->setPostCommentData($data);
                        if (is_array($validate)) {
                            foreach ($validate as $errorMessage) {
                                $session->addError($errorMessage);
                            }
                        } else {
                            $session->addError($this->__('Unable to post the comment.'));
                        }
                    }
                } else {
                    $session->addError($this->__('Guest comments are not allowed'));
                }
            } else {
                $session->addError($this->__('This post does not allow comments'));
            }
        }
        $this->_redirectReferer();
    }
}
