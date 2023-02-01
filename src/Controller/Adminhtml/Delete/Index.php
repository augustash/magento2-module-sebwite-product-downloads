<?php

namespace Sebwite\ProductDownloads\Controller\Adminhtml\Delete;

use Sebwite\ProductDownloads\Model\Download;
use Sebwite\ProductDownloads\Model\DownloadFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;

/**
 * Index action.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends Action
{
    /**
     * @var \Sebwite\ProductDownloads\Model\Download
     */
    protected $download;

    /**
     * @var \Sebwite\ProductDownloads\Model\DownloadFactory
     */
    protected $downloadFactory;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Sebwite\ProductDownloads\Model\Download $download
     * @param \Sebwite\ProductDownloads\Model\DownloadFactory $downloadFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Download $download,
        DownloadFactory $downloadFactory,
        Context $context
    ) {
        $this->download = $download;
        $this->downloadFactory = $downloadFactory;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($downloadId = $this->getRequest()->getParam('download_id')) {
            $name = "";

            try {
                /** @var \Sebwite\ProductDownloads\Model\Download $download */
                $download = $this->downloadFactory->create();
                $download->load($downloadId);
                $name = $download->getName();
                $productId = $download['product_id'];
                $download->delete();
                $this->messageManager->addSuccessMessage(__('The download has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_sebwite_productdownloads_download_on_delete',
                    ['name' => $name, 'status' => 'success']
                );

                $resultRedirect->setPath('catalog/product/edit/*', ['id' => $productId, 'active_tab' => 'downloads']);

                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_sebwite_productdownloads_delete_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('catalog/product/edit/', ['id' => $productId, 'active_tab' => 'downloads']);

                return $resultRedirect;
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a download to delete.'));

        // go to grid
        $resultRedirect->setPath('catalog/product/index');

        return $resultRedirect;
    }
}
