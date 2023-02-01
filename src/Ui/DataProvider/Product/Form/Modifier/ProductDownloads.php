<?php

namespace Sebwite\ProductDownloads\Ui\DataProvider\Product\Form\Modifier;

use Sebwite\ProductDownloads\Model\Download;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Fieldset;

class ProductDownloads extends AbstractModifier
{
    /**
     * @var int
     */
    protected $_amountDownloads = 10;

    /**
     * @var \Sebwite\ProductDownloads\Model\Download
     */
    protected $download;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor.
     *
     * @param \Sebwite\ProductDownloads\Model\Download $download
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Download $download,
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->download = $download;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    /**
     * Modify meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $fields = $this->getDownloadFields();
        $meta['test_fieldset_name'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Product Downloads'),
                        'sortOrder'     => 50,
                        'collapsible'   => true,
                        'componentType' => Fieldset::NAME,
                    ]
                ]
            ],
            'children'  => $fields
        ];

        return $meta;
    }

    /**
     * Get download fields.
     *
     * @return array
     */
    public function getDownloadFields()
    {
        $fields = [];
        $product = $this->registry->registry('current_product');

        $downloads = $this->download->getDownloadsForProduct($product->getId());
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        for ($i = 0; $i < $this->_amountDownloads; $i++) {
            if (isset($downloads[$i])) {
                $key = \sprintf('data.product.remove_download[%s]', $i);
                $fields[$key] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'checkbox',
                                'componentType' => 'field',
                                'description' => $downloads[$i]['download_file'],
                                'dataScope' => \sprintf(
                                    'data.product.remove_download][%s]',
                                    $downloads[$i]['download_id']
                                ),
                                'checked' => true,
                                'value' => true,
                                'visible' => 1,
                                'required' => 0,
                                'label' => __('File %1', $i + 1),
                                'comment' => \sprintf(
                                    '<a href="%s%s" target="_blank">%s</a>',
                                    $store->getBaseUrl(),
                                    $this->download->getUrl($downloads[$i]),
                                    $downloads[$i]['download_file']
                                )
                            ]
                        ]
                    ]
                ];
            } else {
                $key = \sprintf('sebwite.downloads[%s]', $i);
                $fields[$key] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement'   => 'file',
                                'dataScope'     => 'sebwite.downloads[]',
                                'componentType' => 'field',
                                'visible'       => 1,
                                'required'      => 0,
                                'label'         => __('File %1', $i + 1)
                            ]
                        ]
                    ]
                ];
            }
        }

        return $fields;
    }

    /**
     * Modify data (interface method).
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
