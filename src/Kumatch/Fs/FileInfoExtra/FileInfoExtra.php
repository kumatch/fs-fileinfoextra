<?php

namespace Kumatch\Fs\FileInfoExtra;

use SplFileInfo;
use finfo;

/**
 * Class FileInfoExtra
 * @package Kumatch\Fs\FileInfoExtra
 */
class FileInfoExtra extends SplFileInfo
{
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
     * @return string|bool
     */
    public function getMimeType()
    {
        if (is_null($this->mimeType)) {
            if (!$this->exists()) {
                return false;
            }

            if ($this->isDir()) {
                return false;
            }

            $finfo = new finfo(\FILEINFO_SYMLINK | \FILEINFO_MIME_TYPE);

            return $finfo->file($this->getPathname());
        } else {
            return $this->mimeType;
        }
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