<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Config;

use Darkheim\Infrastructure\Config\JsonConfigReader;
use PHPUnit\Framework\TestCase;

class JsonConfigReaderTest extends TestCase
{
    private string $dir;
    private JsonConfigReader $reader;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/dh_json_test_' . uniqid('', true) . '/';
        mkdir($this->dir, 0o777, true);
        $this->reader = new JsonConfigReader();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    public function testValidJson(): void
    {
        $path = $this->dir . 'valid.json';
        file_put_contents($path, json_encode(['key' => 'value', 'num' => 42]));
        $result = $this->reader->readFile($path);
        $this->assertSame(['key' => 'value', 'num' => 42], $result);
    }

    public function testMissingFile(): void
    {
        $this->assertNull($this->reader->readFile($this->dir . 'missing.json'));
    }

    public function testEmptyFile(): void
    {
        $path = $this->dir . 'empty.json';
        file_put_contents($path, '');
        $this->assertNull($this->reader->readFile($path));
    }

    public function testInvalidJson(): void
    {
        $path = $this->dir . 'invalid.json';
        file_put_contents($path, '{not valid json}');
        $this->assertNull($this->reader->readFile($path));
    }

    public function testJsonArray(): void
    {
        $path = $this->dir . 'array.json';
        file_put_contents($path, json_encode([1, 2, 3]));
        $result = $this->reader->readFile($path);
        $this->assertSame([1, 2, 3], $result);
    }
}
