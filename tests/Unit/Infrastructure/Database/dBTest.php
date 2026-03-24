<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use Darkheim\Infrastructure\Database\dB;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

/**
 * Unit-tests for the dB PDO wrapper.
 * Uses mock PDO/PDOStatement — no real database or driver required.
 * MSSQL-specific SQL lives in business-logic classes (tested separately with mocked dB).
 */
class dBTest extends TestCase
{
    private function make(): array
    {
        $sut  = new \ReflectionClass(dB::class)->newInstanceWithoutConstructor();
        $pdo  = $this->createMock(\PDO::class);
        $stmt = $this->createMock(\PDOStatement::class);

        new \ReflectionProperty(dB::class, 'db')->setValue($sut, $pdo);
        new \ReflectionProperty(dB::class, 'dead')->setValue($sut, false);
        new \ReflectionProperty(dB::class, '_enableErrorLogs')->setValue($sut, false);

        return [$sut, $pdo, $stmt];
    }

    // ── query ────────────────────────────────────────────────────────────────

    public function testQueryReturnsTrueOnSuccess(): void
    {
        [$sut, $pdo, $stmt] = $this->make();
        $stmt->method('execute')->willReturn(true);
        $pdo->method('prepare')->willReturn($stmt);

        $this->assertTrue($sut->query('UPDATE Foo SET bar = ?', [1]));
    }

    public function testQueryReturnsFalseWhenPrepareFails(): void
    {
        [$sut, $pdo] = $this->make();
        $pdo->method('prepare')->willReturn(false);
        $pdo->method('errorInfo')->willReturn(['HY000', 1, 'General error']);
        $pdo->method('getAttribute')->willReturn('dblib');

        $this->assertFalse($sut->query('INVALID SQL'));
    }

    public function testQueryReturnsFalseWhenExecuteFails(): void
    {
        [$sut, $pdo, $stmt] = $this->make();
        $stmt->method('execute')->willReturn(false);
        $stmt->method('errorInfo')->willReturn(['42000', 102, 'Syntax error']);
        $pdo->method('prepare')->willReturn($stmt);
        $pdo->method('getAttribute')->willReturn('dblib');

        $this->assertFalse($sut->query('BROKEN'));
    }

    // ── query_fetch ───────────────────────────────────────────────────────────

    public function testQueryFetchReturnsRowsOnSuccess(): void
    {
        [$sut, $pdo, $stmt] = $this->make();
        $rows               = [['Name' => 'Player1', 'cLevel' => '400']];
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->with(\PDO::FETCH_ASSOC)->willReturn($rows);
        $pdo->method('prepare')->willReturn($stmt);

        $this->assertSame($rows, $sut->query_fetch('SELECT * FROM Character'));
    }

    public function testQueryFetchReturnsNullWhenNoRows(): void
    {
        [$sut, $pdo, $stmt] = $this->make();
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn([]);
        $pdo->method('prepare')->willReturn($stmt);

        $this->assertNull($sut->query_fetch('SELECT * FROM Character WHERE Name = ?', ['nobody']));
    }

    public function testQueryFetchReturnsFalseWhenPrepareFails(): void
    {
        [$sut, $pdo] = $this->make();
        $pdo->method('prepare')->willReturn(false);
        $pdo->method('errorInfo')->willReturn(['HY000', 1, 'error']);
        $pdo->method('getAttribute')->willReturn('dblib');

        $this->assertFalse($sut->query_fetch('BROKEN'));
    }

    // ── query_fetch_single ────────────────────────────────────────────────────

    public function testQueryFetchSingleReturnsFirstRow(): void
    {
        [$sut, $pdo, $stmt] = $this->make();
        $rows               = [['Name' => 'Alice'], ['Name' => 'Bob']];
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($rows);
        $pdo->method('prepare')->willReturn($stmt);

        $result = $sut->query_fetch_single('SELECT * FROM Character');
        $this->assertSame(['Name' => 'Alice'], $result);
    }

    public function testQueryFetchSingleReturnsNullWhenEmpty(): void
    {
        [$sut, $pdo, $stmt] = $this->make();
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn([]);
        $pdo->method('prepare')->willReturn($stmt);

        $this->assertNull($sut->query_fetch_single('SELECT * FROM Character WHERE Name = ?', ['nobody']));
    }

    // ── normalizeParams (via query) ───────────────────────────────────────────

    public function testEmptyStringParamsNormalisedToEmptyArray(): void
    {
        [$sut, $pdo, $stmt] = $this->make();
        $stmt->method('execute')
            ->with([])        // must receive [] not ''
            ->willReturn(true);
        $pdo->method('prepare')->willReturn($stmt);

        $this->assertTrue($sut->query('SELECT 1', ''));
    }
}
