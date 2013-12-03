<?php

namespace Kumatch\Fs\FileInfoExtra;

use SplFileInfo;
use finfo;
use Skyzyx\Components\Mimetypes\Mimetypes;

/**
 * Class FileInfoExtra
 * @package Kumatch\Fs\FileInfoExtra
 */
class FileInfoExtra extends SplFileInfo
{
    const MIME_TYPE_FINFO = 1;
    const MIME_TYPE_EXTENSION_MAP = 2;

    /** @var string */
    protected $mimeType;

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getPathname());
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        if ($this->_hasExtensionMethod()) {
            return parent::getExtension();
        } else {
            return $this->_getExtension();
        }
    }

    /**
     * @param int $runType
     * @return string|bool
     */
    public function getMimeType($runType = null)
    {
        if (!is_null($this->mimeType)) {
            return $this->mimeType;
        }

        if (!$this->exists()) {
            return false;
        }

        if ($this->isDir()) {
            return false;
        }

        if (is_null($runType)) {
            $runType = static::MIME_TYPE_FINFO;
        }

        switch ((int)$runType) {
            case static::MIME_TYPE_EXTENSION_MAP:
                $mimeType = $this->getExtensionsMimeType();
                break;

            case static::MIME_TYPE_FINFO:
            default:
                $mimeType = $this->getFinfoMimeType();
                break;
        }

        return $mimeType;
    }

    /**
     * @param $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }


    /**
     * @return string|bool
     */
    public function getMimeEncoding()
    {
        if (is_null($this->mimeType)) {
            if (!$this->exists()) {
                return false;
            }

            if ($this->isDir()) {
                return false;
            }

            $finfo = new finfo(\FILEINFO_SYMLINK | \FILEINFO_MIME_ENCODING);

            return $finfo->file($this->getPathname());
        } else {
            return $this->mimeType;
        }
    }

    /**
     * @param string $algo
     * @param bool $raw_output
     * @return string|bool
     */
    public function getFileHash($algo, $raw_output = false)
    {
        if (!$this->exists()) {
            return false;
        }

        if ($this->isDir()) {
            return false;
        }

        return hash_file($algo, $this->getPathname(), $raw_output);
    }

    /**
     * @param string $algo
     * @param string $key
     * @param bool $raw_output
     * @return string|bool
     */
    public function getFileHmac($algo, $key, $raw_output = false)
    {
        if (!$this->exists()) {
            return false;
        }

        if ($this->isDir()) {
            return false;
        }

        return hash_hmac_file($algo, $this->getPathname(), $key, $raw_output);
    }

    /**
     * @return string
     */
    protected function getFinfoMimeType()
    {
        $finfo = new finfo(\FILEINFO_SYMLINK | \FILEINFO_MIME_TYPE);

        return $finfo->file($this->getPathname());
    }

    /**
     * @return string
     */
    protected function getExtensionsMimeType()
    {
        return Mimetypes::getInstance()->fromFilename($this->getPathname());
    }

    /**
     * @return bool
     */
    protected function _hasExtensionMethod()
    {
        return version_compare(PHP_VERSION, '5.3.6', '>=');
    }

    /**
     * @return string
     */
    protected function _getExtension()
    {
        return pathinfo($this->getFilename(), PATHINFO_EXTENSION);
    }
}