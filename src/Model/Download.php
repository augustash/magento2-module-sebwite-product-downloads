<?php

namespace Sebwite\ProductDownloads\Model;

use Sebwite\ProductDownloads\Model\ResourceModel\Download as ResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Download extends AbstractModel implements IdentityInterface
{
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;
    public const CACHE_TAG = 'sebwite_product_download';

    /**
     * @var string
     */
    protected $_cacheTag = 'sebwite_product_download';

    /**
     * @var string
     */
    protected $_eventPrefix = 'sebwite_product_download';

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var string
     */
    protected $uploadFolder = 'sebwite/productdownloads/';

    /**
     * Constructor.
     *
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        FilterManager $filter,
        Context $context,
        Registry $registry,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->filter = $filter;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Prepare download's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Get Download url
     *
     * @param array $download
     * @return string
     */
    public function getUrl(array $download)
    {
        return \sprintf(
            'pub/media/%s%s',
            $this->uploadFolder,
            $download['download_url']
        );
    }

    /**
     * Check if download url key exists.
     *
     * Return download id if download exists.
     *
     * @param string $urlKey
     * @return string|int
     */
    public function checkUrlKey(string $urlKey)
    {
        /** @var \Sebwite\ProductDownloads\Model\ResourceModel\Download $resource */
        $resource = $this->getResource();
        return $resource->checkUrlKey($urlKey);
    }

    /**
     * Get downloads
     *
     * @param string|int $downloadId
     * @return array
     */
    public function getDownloadsForProduct($downloadId)
    {
        /** @var \Sebwite\ProductDownloads\Model\ResourceModel\Download $resource */
        $resource = $this->getResource();
        return $resource->getDownloadsForProduct($downloadId);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
