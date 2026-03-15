<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Config;

use Darkheim\Infrastructure\Config\XmlConfigReader;
use PHPUnit\Framework\TestCase;

class XmlConfigReaderTest extends TestCase
{
    private string $dir;
    private XmlConfigReader $reader;

    protected function setUp(): void
    {
        $this->dir    = sys_get_temp_dir() . '/dh_xml_test_' . uniqid('', true) . '/';
        mkdir($this->dir, 0777, true);
        $this->reader = new XmlConfigReader();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $f) @unlink($f);
        @rmdir($this->dir);
    }

    public function testValidXml(): void
    {
        $path = $this->dir . 'valid.xml';
        file_put_contents($path, '<?xml version="1.0"?><config><active>1</active><limit>10</limit></config>');
        $result = $this->reader->readFile($path);
        $this->assertIsArray($result);
        $this->assertSame('1', $result['active']);
        $this->assertSame('10', $result['limit']);
    }

    public function testMissingFile(): void
    {
        $this->assertNull($this->reader->readFile($this->dir . 'missing.xml'));
    }

    public function testEmptyFile(): void
    {
        $path = $this->dir . 'empty.xml';
        file_put_contents($path, '');
        $this->assertNull($this->reader->readFile($path));
    }

    public function testInvalidXml(): void
    {
        $path = $this->dir . 'invalid.xml';
        file_put_contents($path, '<unclosed>');
        $this->assertNull($this->reader->readFile($path));
    }
}

