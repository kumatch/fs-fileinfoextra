<?php

namespace Kumatch\Test\Fs\FileInfoExtra;

use Kumatch\Fs\FileInfoExtra\FileInfoExtra;
use Kumatch\Fs\FileInfoExtra\HashAlgorithm;

class FileInfoExtraTest extends \PHPUnit_Framework_TestCase
{
    protected $skelton;

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function existsProvider()
    {
        return array(
            array(__DIR__ . '/samples/1.txt', true),
            array(__DIR__, true),
            array(__DIR__ . '/samples/not_exists.txt', false),
            array('dummy.txt', false)
        );
    }

    /**
     * @test
     * @dataProvider existsProvider
     */
    public function exists1($path, $result)
    {
        $file = new FileInfoExtra($path);

        $this->assertEquals($result, $file->exists());
    }

    /**
     * @test
     * @dataProvider existsProvider
     */
    public function exists2($path, $result)
    {
        $info = new \SplFileInfo($path);
        /** @var \Kumatch\Fs\FileInfoExtra\FileInfoExtra $file */
        $file = $info->getFileInfo('\Kumatch\Fs\FileInfoExtra\FileInfoExtra');

        $this->assertEquals($result, $file->exists());
    }

    public function extensionProvider()
    {
        return array(
            array('foo.txt', 'txt'),
            array('bar.tar.gz', 'gz'),
            array('bazqux', '')
        );
    }

    /**
     * @test
     * @dataProvider extensionProvider
     */
    public function getExtension($path, $result)
    {
        $file = new FileInfoExtra($path);

        $this->assertEquals($result, $file->getExtension());
    }



    public function mimeTypeProvider()
    {
        return array(
            array(__DIR__ . '/samples/1.txt', 'text/plain'),
            array(__DIR__ . '/samples/1.png', 'image/png'),
            array(__DIR__ . '/samples/1.tar.gz', 'application/x-gzip'),
            array(__DIR__, false),
            array('dummy.txt', false)
        );
    }

    /**
     * @test
     * @dataProvider mimeTypeProvider
     */
    public function getMimeTypeByFinfo($path, $result)
    {
        $file = $this->getMockBuilder('Kumatch\Fs\FileInfoExtra\FileInfoExtra')
            ->setMethods(array('getExtensionsMimeType'))
            ->setConstructorArgs(array($path))
            ->getMock();
        $file->expects($this->never())
            ->method('getExtensionsMimeType');

        /** @type FileInfoExtra $file */
        $this->assertEquals($result, $file->getMimeType());
        $this->assertEquals($result, $file->getMimeType(FileInfoExtra::MIME_TYPE_FINFO));
    }

    /**
     * @test
     * @dataProvider mimeTypeProvider
     */
    public function getMimeTypeByExtensionMap($path, $result)
    {
        $file = $this->getMockBuilder('Kumatch\Fs\FileInfoExtra\FileInfoExtra')
            ->setMethods(array('getFinfoMimeType'))
            ->setConstructorArgs(array($path))
            ->getMock();
        $file->expects($this->never())
            ->method('getFinfoMimeType');

        /** @type FileInfoExtra $file */
        $this->assertEquals($result, $file->getMimeType(FileInfoExtra::MIME_TYPE_EXTENSION_MAP));
    }

    /**
     * @test
     * @dataProvider mimeTypeProvider
     */
    public function setAndGetMimeType($path, $result)
    {
        $file = new FileInfoExtra($path);
        $mimeType = 'kumatch/test';

        $this->assertEquals($result, $file->getMimeType());

        $file->setMimeType($mimeType);

        $this->assertEquals($mimeType, $file->getMimeType());

        $file->setMimeType(null);

        $this->assertEquals($result, $file->getMimeType());
    }


    public function mimeEncodingProvider()
    {
        return array(
            array(__DIR__ . '/samples/1.txt', 'us-ascii'),
            array(__DIR__ . '/samples/1.png', 'binary'),
            array(__DIR__ . '/samples/1.tar.gz', 'binary'),
            array(__DIR__, false),
            array('dummy.txt', false)
        );
    }

    /**
     * @test
     * @dataProvider mimeEncodingProvider
     */
    public function getMimeEncoding($path, $result)
    {
        $file = new FileInfoExtra($path);

        $this->assertEquals($result, $file->getMimeEncoding());
    }




    public function fileDigestProvider()
    {
        return array(
            array(__DIR__ . '/samples/1.txt', true),
            array(__DIR__ . '/samples/1.png', true),
            array(__DIR__ . '/samples/1.tar.gz', true),
            array(__DIR__, false),
            array('dummy.txt', false)
        );
    }

    /**
     * @test
     * @dataProvider fileDigestProvider
     */
    public function getFileHash($path, $valid)
    {
        $file = new FileInfoExtra($path);

        if ($valid) {
            $this->assertEquals(md5_file($path), $file->getFileHash('md5'));
            $this->assertEquals(sha1_file($path), $file->getFileHash(HashAlgorithm::SHA1));
            $this->assertEquals(hash_file('haval160,4', $path), $file->getFileHash(HashAlgorithm::HAVAL160_4));

            $this->assertNotEquals(sha1_file($path, true), $file->getFileHash(HashAlgorithm::SHA1));
            $this->assertEquals(sha1_file($path, true), $file->getFileHash(HashAlgorithm::SHA1, true));
        } else {
            $this->assertFalse($file->getFileHash('md5'));
            $this->assertFalse($file->getFileHash(HashAlgorithm::SHA1));
        }
    }

    /**
     * @test
     * @dataProvider fileDigestProvider
     */
    public function getFileHmac($path, $valid)
    {
        $file = new FileInfoExtra($path);
        $key = "fileinfoextra_secret";
        $invalid_key = "invalid_secret";

        if ($valid) {
            $this->assertEquals(hash_hmac_file('md5',  $path, $key), $file->getFileHmac('md5', $key));
            $this->assertNotEquals(hash_hmac_file('md5',  $path, $invalid_key), $file->getFileHmac('md5', $key));

            $this->assertEquals(hash_hmac_file('sha1', $path, $key), $file->getFileHmac(HashAlgorithm::SHA1, $key));
            $this->assertEquals(hash_hmac_file('haval160,4', $path, $key), $file->getFileHmac(HashAlgorithm::HAVAL160_4, $key));

            $this->assertNotEquals(hash_hmac_file("sha1", $path, $key, true), $file->getFileHmac(HashAlgorithm::SHA1, $key));
            $this->assertEquals(hash_hmac_file("sha1", $path, $key, true), $file->getFileHmac(HashAlgorithm::SHA1, $key, true));
        } else {
            $this->assertFalse($file->getFileHmac('md5', $key));
            $this->assertFalse($file->getFileHmac(HashAlgorithm::SHA1, $key));
        }
    }
}
