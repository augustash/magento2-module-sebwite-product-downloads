<?php

namespace Sebwite\ProductDownloads\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Store\Model\Store;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime as StdLibDate;
use Magento\Framework\Stdlib\DateTime as StdLibDateTime;

class Download extends AbstractDb
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        ResourceConnection $resource,
        StdLibDate $date,
        StdLibDateTime $dateTime,
        Context $context,
        $connectionName = null
    ) {
        $this->resource = $resource;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('sebwite_product_downloads', 'download_id');
    }

    /**
     * Load an object using 'product_id' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel|\Sebwite\ProductDownloads\Model\Download $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field != null) {
            $field = 'product_id';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve all Downloads for product
     *
     * @param string|int $id
     * @return array
     */
    public function getDownloadsForProduct($id)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $conn */
        $conn = $this->resource->getConnection();
        $select = $conn->select()->from($this->getMainTable())->where('product_id = :product_id');
        $binds = ['product_id' => (int) $id];

        return $this->resource->getConnection()->fetchAll($select, $binds);
    }

    /**
     * Check if download url key exist.
     *
     * Return download id if download exists
     *
     * @param string $urlKey
     * @return string|int
     */
    public function checkUrlKey($urlKey)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->_getLoadByUrlKeySelect($urlKey);
        $select->reset(\Laminas\Db\Sql\Select::COLUMNS)->columns('download_id')->limit(1);

        return $this->resource->getConnection()->fetchOne($select);
    }

    /**
     * Retrieve load select with filter by url_key and activity
     *
     * @param string $urlKey
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadByUrlKeySelect(string $urlKey)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $conn */
        $conn = $this->resource->getConnection();
        $select = $conn->select()->from(
            ['spd' => $this->getMainTable()]
        )->where(
            'spd.download_url = ?',
            $urlKey
        );

        return $select;
    }

    /**
     * Process download data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['download_id = ?' => (int) $object->getId()];
        $this->resource->getConnection()->delete($this->getTable('sebwite_product_downloads'), $condition);

        return parent::_beforeDelete($object);
    }
}
