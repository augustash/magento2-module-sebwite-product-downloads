<?php

namespace Sebwite\ProductDownloads\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

// phpcs:ignoreFile

/**
 * Install schema (obsolete)
 *
 * Use the etc/db_schema.xml instead.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'downloadable_link'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('sebwite_product_downloads'))
            ->addColumn('download_id', Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Download ID')
            ->addColumn('product_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Product ID')
            ->addColumn('number_of_downloads', Table::TYPE_INTEGER, null, ['nullable' => true], 'Number of downloads')->addColumn('download_url', Table::TYPE_TEXT, 255, [], 'Download Url')
            ->addColumn('download_file', Table::TYPE_TEXT, 255, [], 'Download File')
            ->addColumn('download_type', Table::TYPE_TEXT, 20, [], 'Download Type')
            ->addIndex($installer->getIdxName('sebwite_product_downloads', ['product_id']), ['product_id'])
            ->setComment('Product downloads table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
