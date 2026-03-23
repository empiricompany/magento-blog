<?php

class Mageplaza_BetterBlog_Model_Import
{
    /**
     * Process import from AW Blog extension
     */
    public function aw()
    {
        $categories = Mage::getModel('blog/cat')->getCollection();
        foreach ($categories as $_cat) {
            $data = array(
                'name' => $_cat->getData('title'),
                'url_key' => $_cat->getData('identifier'),
                'meta_keywords' => $_cat->getData('meta_keywords'),
                'meta_description' => $_cat->getData('meta_description'),
                'status' => 0,
                'parent_id' => 1,
                'level' => 1,
            );
            $category = Mage::getModel('mageplaza_betterblog/category');
            $category->setData($data);
            try {
                $category->save();
                $category->setPath('1/' . $category->getId());
                $category->save();
                $newCatId = $category->getId();
                $posts = $this->_getAllPostsInCategory($_cat->getCatId());
                foreach ($posts as $_post) {
                    $model = Mage::getModel('mageplaza_betterblog/post');
                    $_postData = array(
                        'post_title' => $_post->getData('title'),
                        'post_content' => $_post->getData('post_content'),
                        'post_excerpt' => $_post->getData('short_content'),
                        'url_key' => $_post->getData('identifier'),
                        'created_at' => $_post->getData('created_time'),
                        'updated_at' => $_post->getData('update_time'),
                        'status' => 2,
                        'meta_keywords' => $_post->getData('meta_keywords'),
                        'meta_description' => $_post->getData('meta_description'),
                    );

                    $model->setData($_postData);
                    $model->setAttributeSetId($model->getDefaultAttributeSetId());

                    $model->setCategoriesData(array($newCatId));
                    $model->save();
                    $this->_insertTags($_post->getTags(), $model);
                    $comments = $_post->getComments();
                    $this->_insertComments($comments, $model);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::log('Cannot save category #' . $data['name'] . '. ' . $e->getMessage());
            }
        }
    }

    /**
     * Get all posts in category id
     *
     * @param int $id
     * @return mixed
     */
    protected function _getAllPostsInCategory($id)
    {
        $collection = Mage::getModel('blog/blog')->getCollection()
            ->joinComments();
        $collection->addCatFilter($id);
        return $collection;
    }

    /**
     * Insert tags
     *
     * @param mixed $tags
     * @param Mageplaza_BetterBlog_Model_Post $post
     */
    protected function _insertTags($tags, $post)
    {
        $tagArray = array();
        if (!is_array($tags)) {
            $tags = explode(',', (string)$tags);
        }
        foreach ($tags as $_tag) {
            $_tag = trim((string)$_tag);
            if ($_tag === '') {
                continue;
            }
            $model = Mage::getModel('mageplaza_betterblog/tag')->getCollection()
                ->addFieldToFilter('name', $_tag)
                ->getFirstItem();
            if ($model && $model->getId()) {
                $tagArray[$model->getId()] = array(
                    'position' => ''
                );
            } else {
                $model->setData(
                    array(
                        'name' => $_tag,
                        'status' => 1,
                        'created_at' => Mage::helper('core')->formatDate(now())
                    )
                );
                $model->save();
                if ($model && $model->getId()) {
                    $tagArray[$model->getId()] = array(
                        'position' => ''
                    );
                }
            }
        }
        $post->setTagsData($tagArray);
        try {
            $post->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Insert comments from AW Blog
     *
     * @param mixed $comments
     * @param Mageplaza_BetterBlog_Model_Post $post
     */
    protected function _insertComments($comments, $post)
    {
        if (!$comments) {
            return;
        }
        foreach ($comments as $commentData) {
            try {
                $comment = Mage::getModel('mageplaza_betterblog/post_comment');
                $comment->setData(array(
                    'post_id'    => $post->getId(),
                    'title'      => (string)$commentData->getData('comment'),
                    'comment'    => (string)$commentData->getData('comment'),
                    'name'       => (string)$commentData->getData('user'),
                    'email'      => (string)$commentData->getData('email'),
                    'status'     => (int)$commentData->getData('status'),
                    'created_at' => $commentData->getData('created_time'),
                ));
                $comment->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
}
