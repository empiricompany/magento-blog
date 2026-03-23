<?php
/**
 * Mageplaza_BetterBlog extension
 *
 * Upgrade script 1.2.0 -> 1.3.0
 * - Fix FK type mismatch (SMALLINT -> INTEGER)
 * - Add missing indexes on category, tag, comment tables
 * - Add missing index on post_product.entity_id
 * - Add missing foreign keys (eav_attribute, category.parent_id)
 */
$this->startSetup();

// Fix type mismatch: category_store.category_id SMALLINT -> INTEGER
$this->getConnection()->changeColumn(
    $this->getTable('mageplaza_betterblog/category_store'),
    'category_id', 'category_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Category ID',
    )
);

// Fix type mismatch: tag_store.tag_id SMALLINT -> INTEGER
$this->getConnection()->changeColumn(
    $this->getTable('mageplaza_betterblog/tag_store'),
    'tag_id', 'tag_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Tag ID',
    )
);

// Fix type mismatch: post_comment_store.comment_id SMALLINT -> INTEGER
$this->getConnection()->changeColumn(
    $this->getTable('mageplaza_betterblog/post_comment_store'),
    'comment_id', 'comment_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Comment ID',
    )
);

// Add indexes on category table
$categoryTable = $this->getTable('mageplaza_betterblog/category');
$this->getConnection()->addIndex(
    $categoryTable,
    $this->getIdxName($categoryTable, array('url_key')),
    array('url_key')
);
$this->getConnection()->addIndex(
    $categoryTable,
    $this->getIdxName($categoryTable, array('status')),
    array('status')
);
$this->getConnection()->addIndex(
    $categoryTable,
    $this->getIdxName($categoryTable, array('parent_id')),
    array('parent_id')
);
$this->getConnection()->addIndex(
    $categoryTable,
    $this->getIdxName($categoryTable, array('path')),
    array('path')
);

// Add indexes on tag table
$tagTable = $this->getTable('mageplaza_betterblog/tag');
$this->getConnection()->addIndex(
    $tagTable,
    $this->getIdxName($tagTable, array('url_key')),
    array('url_key')
);
$this->getConnection()->addIndex(
    $tagTable,
    $this->getIdxName($tagTable, array('status')),
    array('status')
);

// Add indexes on post_comment table
$commentTable = $this->getTable('mageplaza_betterblog/post_comment');
$this->getConnection()->addIndex(
    $commentTable,
    $this->getIdxName($commentTable, array('status')),
    array('status')
);
$this->getConnection()->addIndex(
    $commentTable,
    $this->getIdxName($commentTable, array('post_id')),
    array('post_id')
);
$this->getConnection()->addIndex(
    $commentTable,
    $this->getIdxName($commentTable, array('customer_id')),
    array('customer_id')
);

// Add index on post_product.entity_id for reverse lookups (posts by product)
$postProductTable = $this->getTable('mageplaza_betterblog/post_product');
$this->getConnection()->addIndex(
    $postProductTable,
    $this->getIdxName($postProductTable, array('entity_id')),
    array('entity_id')
);

// FK: mageplaza_betterblog_eav_attribute.attribute_id -> eav_attribute.attribute_id
$eavAttrTable = $this->getTable('mageplaza_betterblog/eav_attribute');
$coreEavTable = $this->getTable('eav/attribute');
$conn = $this->getConnection();
$conn->addForeignKey(
    $this->getFkName($eavAttrTable, 'attribute_id', $coreEavTable, 'attribute_id'),
    $eavAttrTable,
    'attribute_id',
    $coreEavTable,
    'attribute_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

// FK: category.parent_id -> category.entity_id (self-referential)
// Clean up orphan parent_id references first
$conn->query("
    UPDATE {$categoryTable} c
    LEFT JOIN {$categoryTable} p ON c.parent_id = p.entity_id
    SET c.parent_id = NULL
    WHERE c.parent_id IS NOT NULL
      AND c.parent_id != 0
      AND p.entity_id IS NULL
");
$conn->query("UPDATE {$categoryTable} SET parent_id = NULL WHERE parent_id = 0");

$conn->modifyColumn($categoryTable, 'parent_id', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned' => false,
    'nullable' => true,
    'default'  => null,
    'comment'  => 'Parent Category ID',
));
$conn->addForeignKey(
    $this->getFkName($categoryTable, 'parent_id', $categoryTable, 'entity_id'),
    $categoryTable,
    'parent_id',
    $categoryTable,
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

$this->endSetup();
