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
     * @testdox ファイル存在可否の確認
     * @dataProvider existsProvider
     */
    public function testExists1($path, $result)
    {
        $file = new FileInfoExtra($path);

        $this->assertEquals($result, $file->exists());
    }

    /**
     * @testdox ファイル存在可否の確認
     * @dataProvider existsProvider
     */
    public function testExists2($path, $result)
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
     * @testdox ファイルの拡張子を取得する
     * @dataProvider extensionProvider
     */
    public function testGetExtension1($path, $result)
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
     * @testdox 自動判別によるMIME-Typeの取得
     * @dataProvider mimeTypeProvider
     */
    public function testMimeType1($path, $result)
    {
        $file = new FileInfoExtra($path);

        $this->assertEquals($result, $file->getMimeType());
    }

    /**
     * @testdox 明示的に指定したMIME-Typeの取得
     * @dataProvider mimeTypeProvider
     */
    public function testMimeType2($path, $result)
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
     * @testdox 自動判別によるMIME-Encodingの取得
     * @dataProvider mimeEncodingProvider
     */
    public function testMimeEncoding1($path, $result)
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
     * @testdox ファイルハッシュダイジェスト
     * @dataProvider fileDigestProvider
     */
    public function testFileHash($path, $valid)
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
     * @testdox ファイル Hmac ダイジェスト
     * @dataProvider fileDigestProvider
     */
    public function testFileHmac($path, $valid)
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
