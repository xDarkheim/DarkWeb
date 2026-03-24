<?php

declare(strict_types=1);

namespace Darkheim\Application\Rankings;

/**
 * Holds a parsed legacy ranking cache file.
 * $entries is a list of indexed rows (metadata row 0 already stripped).
 */
final readonly class RankingCache
{
    public function __construct(
        public int   $timestamp,
        public array $entries,
    ) {}
}
