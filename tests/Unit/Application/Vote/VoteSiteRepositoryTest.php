<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Vote;

use Darkheim\Application\Vote\VoteSiteRepository;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\DbTestHelper;

class VoteSiteRepositoryTest extends TestCase
{
    use DbTestHelper;

    private function make(dB $mockDb): VoteSiteRepository
    {
        /** @var VoteSiteRepository $sut */
        $sut = $this->makeWithDb(VoteSiteRepository::class, $mockDb);
        return $sut;
    }

    public function testFindAllReturnsArrayOnResults(): void
    {
        $row = ['votesite_id' => 1, 'votesite_title' => 'TopG'];
        $db  = $this->createMock(dB::class);
        $db->method('query_fetch')->willReturn([$row]);
        $sut = $this->make($db);
        $this->assertSame([$row], $sut->findAll());
    }

    public function testFindAllReturnsNullWhenEmpty(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch')->willReturn(null);
        $sut = $this->make($db);
        $this->assertNull($sut->findAll());
    }

    public function testFindByIdReturnsRowWhenFound(): void
    {
        $row = ['votesite_id' => 3];
        $db  = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn($row);
        $sut = $this->make($db);
        $this->assertSame($row, $sut->findById(3));
    }

    public function testFindByIdReturnsNullForEmptyId(): void
    {
        $db = $this->createMock(dB::class);
        $db->expects($this->never())->method('query_fetch_single');
        $sut = $this->make($db);
        $this->assertNull($sut->findById(''));
    }

    public function testExistsReturnsTrueWhenFound(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['votesite_id' => 1]);
        $sut = $this->make($db);
        $this->assertTrue($sut->exists(1));
    }

    public function testAddReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);
        $this->assertTrue($sut->add('TopG', 'https://topg.org', 5, 24));
    }

    public function testDeleteReturnsFalseWhenNotExists(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(null);
        $sut = $this->make($db);
        $this->assertFalse($sut->delete(99));
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['votesite_id' => 1]);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);
        $this->assertTrue($sut->delete(1));
    }

    public function testGetTopVotesReturnsArrayOnResults(): void
    {
        $row = ['user_id' => 'testuser', 'count' => 5];
        $db  = $this->createMock(dB::class);
        $db->method('query_fetch')->willReturn([$row]);
        $sut = $this->make($db);
        $this->assertSame([$row], $sut->getTopVotes(5));
    }

    public function testGetTopVotesReturnsNullWhenEmpty(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch')->willReturn(null);
        $sut = $this->make($db);
        $this->assertNull($sut->getTopVotes());
    }
}
