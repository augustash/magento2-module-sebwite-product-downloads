<?php

namespace Sebwite\ProductDownloads\Block\Product;

use Sebwite\ProductDownloads\Model\Download;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Downloads extends Template
{
    /**
     * @var \Sebwite\ProductDownloads\Model\Download
     */
    protected $download;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Sebwite\ProductDownloads\Model\Download $download
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        Download $download,
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->download = $download;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);

        $this->setTabTitle();
    }

    /**
     * Set tab title
     *
     * @return $this
     */
    public function setTabTitle()
    {
        $this->setTitle(__('Downloads'));

        return $this;
    }

    /**
     * Return Downloads
     *
     * @return mixed
     */
    public function getDownloads()
    {
        return $this->download->getDownloadsForProduct($this->getProduct()->getId());
    }

    /**
     * Return current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Get download URL
     *
     * @param mixed $download
     * @return string
     */
    public function getDownloadUrl($download)
    {
        $baseUrl = \sprintf('%s', str_replace('index.php', '', $this->getBaseUrl()));
        return \sprintf('%s%s', $baseUrl, $this->download->getUrl($download));
    }
}
