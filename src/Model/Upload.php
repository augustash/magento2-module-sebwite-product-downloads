<?php

namespace Sebwite\ProductDownloads\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\Exception as FrameworkException;
use Magento\Framework\Validator\Exception as ValidatorException;
use \Magento\Framework\File\UploaderFactory;

class Upload
{
    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var string
     */
    protected $uploadPath;

    /**
     * @var string
     */
    protected $uploadFolder = 'sebwite/productdownloads/';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $fileSystem,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->uploadPath = $fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath($this->uploadFolder);
    }

    /**
     * Upload the file
     *
     * @param mixed $download
     * @return array|bool
     * @throws \Magento\Framework\Validator\Exception
     */
    public function uploadFile($download)
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $download]);
            $uploader->setAllowedExtensions($this->getMimeTimes());
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);

            $result = $uploader->save($this->uploadPath);

            return $result;
        } catch (\Exception $e) {
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                throw new ValidatorException(
                    __(
                        'Disallowed file type, only these file types are allowed: %s.',
                        implode(', ', $this->getMimeTimes())
                    )
                );
            }
        }

        return false;
    }

    /**
     * Return array of mime types.
     *
     * @return array
     */
    public function getMimeTimes()
    {
        $mimeTypes = $this->scopeConfig->getValue('sebwite_productdownloads/general/extension');
        $cleanMimeTypes = [];
        foreach (explode(',', $mimeTypes) as $mimeType) {
            $mimeType = \strtolower(trim($mimeType ?: ''));
            if (!empty($mimeType)) {
                $cleanMimeTypes[] = $mimeType;
            }
        }

        return $cleanMimeTypes;
    }
}
