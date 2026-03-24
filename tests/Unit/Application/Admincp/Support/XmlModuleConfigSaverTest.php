<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Admincp\Support;

use Darkheim\Application\Admincp\Support\XmlModuleConfigSaver;
use PHPUnit\Framework\TestCase;

final class XmlModuleConfigSaverTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/darkcore-xml-saver-' . uniqid('', true);
        mkdir($this->tmpDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        $xmlPath = $this->tmpDir . '/module.xml';
        if (is_file($xmlPath)) {
            @unlink($xmlPath);
        }

        @rmdir($this->tmpDir);
    }

    public function testSaveUpdatesMappedXmlFields(): void
    {
        $xmlPath = $this->tmpDir . '/module.xml';
        file_put_contents($xmlPath, "<config><active>0</active><subject>old</subject></config>");

        $saver = new XmlModuleConfigSaver();
        $saved = $saver->save([
            'xml'    => 'module.xml',
            'base'   => $this->tmpDir . '/',
            'fields' => [
                'setting_1' => 'active',
                'setting_2' => 'subject',
            ],
        ], [
            'setting_1' => '1',
            'setting_2' => 'new-subject',
        ]);

        $this->assertTrue($saved);

        $updated = simplexml_load_string((string) file_get_contents($xmlPath));
        $this->assertInstanceOf(\SimpleXMLElement::class, $updated);
        $this->assertSame('1', (string) $updated->active);
        $this->assertSame('new-subject', (string) $updated->subject);
    }

    public function testSaveReturnsFalseWhenXmlFileCannotBeLoaded(): void
    {
        $saver = new XmlModuleConfigSaver();

        $saved = $saver->save([
            'xml'    => 'missing.xml',
            'base'   => $this->tmpDir . '/',
            'fields' => [
                'setting_1' => 'active',
            ],
        ], [
            'setting_1' => '1',
        ]);

        $this->assertFalse($saved);
    }
}
