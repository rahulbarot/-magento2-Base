<?php
/**
 * Copyright © MageRahul All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DevAwesome\Base\Helper;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image\Factory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image as CatalogImageHelper;

class Image extends AbstractHelper
{
    const BASE_MEDIA_PATH = 'devawesome/media';

    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 100;
    /**
     * @var bool
     */
    protected $_keepAspectRatio = true;
    /**
     * @var bool
     */
    protected $_keepFrame = true;
    /**
     * @var bool
     */
    protected $_keepTransparency = true;
    /**
     * @var bool
     */
    protected $_constrainOnly = true;
    /**
     * @var array
     */
    protected $_backgroundColor = [255, 255, 255];
    /**
     * @var
     */
    protected $_baseFile;
    /**
     * @var
     */
    protected $_newFile;
    /**
     * @var Factory
     */
    protected Factory $_imageFactory;
    /**
     * @var WriteInterface
     */
    protected WriteInterface $_mediaDirectory;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Repository
     */
    protected Repository $_assetRepo;
    private CatalogImageHelper $catalogImageHelper;

    /**
     * Image constructor.
     * @param Context $context
     * @param Factory $imageFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Repository $assetRepo
     * @param CatalogImageHelper $catalogImageHelper
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Factory $imageFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        Repository $assetRepo,
        CatalogImageHelper $catalogImageHelper
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->_request = $context->getRequest();
        $this->_assetRepo = $assetRepo;
        $this->catalogImageHelper = $catalogImageHelper;
        parent::__construct($context);
    }

    /**
     * @param $baseFile
     * @return $this
     */
    public function init($baseFile): static
    {
        $this->_newFile = '';
        $this->_baseFile = $baseFile;
        return $this;
    }

    /**
     * @param $width
     * @param null $height
     * @param null $keepFrame
     * @return $this
     */
    public function resize($width, $height = null, $keepFrame = null): static
    {
        if ($this->_baseFile) {
            $pathinfo = pathinfo(($this->_baseFile));
            if (isset($pathinfo) && $pathinfo['extension'] == 'webp') {
                $this->_newFile = $this->_baseFile;
            } else {
                $path = self::BASE_MEDIA_PATH . '/cache/' . $width . 'x' . $height;
                if (null !== $keepFrame) {
                    $path .= '_' . (int)$keepFrame;
                }

                $this->_newFile = $path . '/' . $this->_baseFile;
                if (!$this->fileExists($this->_newFile)) {
                    $this->resizeBaseFile($width, $height, $keepFrame);
                }
            }
        }
        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @param $keepFrame
     * @return $this
     */
    protected function resizeBaseFile($width, $height, $keepFrame): static
    {
        if (!$this->fileExists($this->_baseFile)) {
            $this->_baseFile = null;
            return $this;
        }

        if (null === $keepFrame) {
            $keepFrame = $this->_keepFrame;
        }

        $processor = $this->_imageFactory->create(
            $this->_mediaDirectory->getAbsolutePath($this->_baseFile)
        );
        $processor->keepAspectRatio($this->_keepAspectRatio);
        $processor->keepFrame((bool)$keepFrame);
        $processor->keepTransparency($this->_keepTransparency);
        $processor->constrainOnly($this->_constrainOnly);
        $processor->backgroundColor($this->_backgroundColor);
        $processor->quality($this->_quality);
        $processor->resize($width, $height);

        $newFile = $this->_mediaDirectory->getAbsolutePath($this->_newFile);
        $processor->save($newFile);
        unset($processor);

        return $this;
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function fileExists($filename): bool
    {
        return $this->_mediaDirectory->isFile($filename);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function __toString()
    {
        $url = "";
        if ($this->_baseFile) {
            $url = $this->getMediaUrl($this->_newFile);
        }
        return $url;
    }

    /**
     * @param $file
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl($file): string
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        ) . $file;
    }

    /**
     * @param $file
     * @return mixed
     */
    public function getFileSize($file): mixed
    {
        try {
            return $this->_mediaDirectory->stat($file)['size'];
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return bool|string
     */
    public function getViewFileUrl($fileId, array $params = []): bool|string
    {
        try {
            $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->_logger->critical($e);
            return false;
        }
    }
    /**
     * Retrieve url of a file
     *
     * @return string
     */
    public function getMediaAbsolutePath(): string
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_baseFile);
    }

    /**
     * @param $type
     * @return string
     */
    public function getPlaceHolderImage($type): string
    {
        return $this->catalogImageHelper->getDefaultPlaceholderUrl($type);
    }
}
