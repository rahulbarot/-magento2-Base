<?php
/**
 * Copyright © MageRahul All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DevAwesome\Base\Model;

use Exception;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;

/**
 * Class AbstractImageUpload
 * @package DevAwesome\Base\Model
 */
class AbstractImageUpload extends ImageUploader
{
    /**
     * @inheritdoc
     */
    public function moveFileFromTmp($imageName, $returnRelativePath = false): string
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();
        $validName = $this->getValidNewFileName($basePath, $imageName);

        $baseImagePath = $this->getFilePath($basePath, $validName);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $imageName);

        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $returnRelativePath ? $baseImagePath : $validName;
    }

    /**
     * @param string $fileName
     *
     * @return string
     * @throws FileSystemException
     */
    public function duplicateFile($fileName): string
    {
        $basePath = $this->getBasePath();
        $validName = $this->getValidNewFileName($basePath, $fileName);

        $oldName = $this->getFilePath($basePath, $fileName);
        $newName = $this->getFilePath($basePath, $validName);

        $this->mediaDirectory->copyFile(
            $oldName,
            $newName
        );

        return $validName;
    }

    /**
     * @param string $basePath
     * @param string $imageName
     *
     * @return string
     */
    private function getValidNewFileName($basePath, $imageName): string
    {
        $basePath = $this->mediaDirectory->getAbsolutePath($basePath) . DIRECTORY_SEPARATOR . $imageName;

        return Uploader::getNewFileName($basePath);
    }
}
