<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Admincp\Support;

use Darkheim\Application\Admincp\Support\AdmincpConfigurationChecker;
use PHPUnit\Framework\TestCase;

final class AdmincpConfigurationCheckerTest extends TestCase
{
    private string $existingWritablePath;
    private string $missingPath = '/missing-file.txt';
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir              = __PUBLIC_DIR__;
        $this->existingWritablePath = '/tmp-admincp-check.txt';
        file_put_contents($this->baseDir . ltrim($this->existingWritablePath, '/'), 'ok');
    }

    protected function tearDown(): void
    {
        @unlink($this->baseDir . ltrim($this->existingWritablePath, '/'));
    }

    public function testErrorsReturnsMissingFileAndCurlMessages(): void
    {
        $checker = new AdmincpConfigurationChecker([
            $this->existingWritablePath,
            $this->missingPath,
        ], false, $this->baseDir);

        $errors = $checker->errors();

        $this->assertCount(2, $errors);
        $this->assertStringContainsString('[Not Found]', $errors[0]);
        $this->assertStringContainsString($this->missingPath, $errors[0]);
        $this->assertStringContainsString('cURL extension is not loaded', $errors[1]);
    }

    public function testEnsureValidPassesWhenEverythingIsWritableAndCurlExists(): void
    {
        $checker = new AdmincpConfigurationChecker([
            $this->existingWritablePath,
        ], true, $this->baseDir);

        $checker->ensureValid();

        $this->assertTrue(true);
    }

    public function testEnsureValidThrowsWhenErrorsExist(): void
    {
        $checker = new AdmincpConfigurationChecker([
            $this->missingPath,
        ], true, $this->baseDir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The following errors ocurred:');

        $checker->ensureValid();
    }

    public function testFallbackBaseDirPointsToProjectRoot(): void
    {
        $checker = new AdmincpConfigurationChecker();

        $resolver = \Closure::bind(
            function (): string {
                return $this->baseDir();
            },
            $checker,
            AdmincpConfigurationChecker::class,
        );

        $baseDir = $resolver();

        $this->assertIsString($baseDir);
        $this->assertTrue(is_dir($baseDir . 'config'));
        $this->assertFileExists($baseDir . 'config/admincp-layout.php');
    }
}
