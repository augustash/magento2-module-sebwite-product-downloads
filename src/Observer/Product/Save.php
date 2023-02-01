<?php

namespace Sebwite\ProductDownloads\Observer\Product;

use Sebwite\ProductDownloads\Model\Upload;
use Sebwite\ProductDownloads\Model\DownloadFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class Save implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Sebwite\ProductDownloads\Model\Upload
     */
    protected $upload;

    /**
     * @var \Sebwite\ProductDownloads\Model\DownloadFactory
     */
    protected $downloadFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Sebwite\ProductDownloads\Model\Upload $upload
     * @param \Sebwite\ProductDownloads\Model\DownloadFactory $downloadFactory
     */
    public function __construct(
        HttpRequest $httpRequest,
        Registry $coreRegistry,
        Upload $upload,
        DownloadFactory $downloadFactory
    ) {
        $this->httpRequest = $httpRequest;
        $this->coreRegistry = $coreRegistry;
        $this->upload = $upload;
        $this->downloadFactory = $downloadFactory;
    }

    /**
     * Save product data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $downloads = $this->httpRequest->getFiles('downloads', -1);
        $postData = $observer->getDataObject();

        // Get current product
        $product = $this->coreRegistry->registry('product');

        // Delete old downloads
        $this->deleteOldDownloads($postData);

        if ($downloads != '-1') {
            $this->addDownloads($downloads, $product);
        }
    }

    /**
     * Add downloads
     *
     * @param array $downloads
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function addDownloads(array $downloads, Product $product): void
    {
        $productId = $product->getId();

        // Loop through uploaded downlaods
        foreach ($downloads as $download) {
            if ($download[ 'tmp_name' ] === "") {
                continue;
            }

            // Upload file
            $uploadedDownload = $this->upload->uploadFile($download);
            if ($uploadedDownload) {
                // Store date in database
                /** @var \Sebwite\ProductDownloads\Model\Download $download */
                $download = $this->downloadFactory->create();

                $download->setDownloadUrl($uploadedDownload['file']); /* @phpstan-ignore-line */
                $download->setDownloadFile($uploadedDownload['name']); /* @phpstan-ignore-line */
                $download->setDownloadType($uploadedDownload['type']); /* @phpstan-ignore-line */
                $download->setProductId($productId);
                $download->save();
            }
        }
    }

    /**
     * Delete old downloads
     *
     * @param array $postData
     * @return void
     */
    public function deleteOldDownloads($postData): void
    {
        if (isset($postData['remove_download'])) {
            foreach ($postData['remove_download'] as $deleteId => $keep) {
                if ($keep === "0") {
                    /** @var \Sebwite\ProductDownloads\Model\Download $download */
                    $download = $this->downloadFactory->create();
                    $download->load($deleteId);
                    $download->delete();
                }
            }
        }
    }
}
