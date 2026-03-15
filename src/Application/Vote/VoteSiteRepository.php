<?php

declare(strict_types=1);

namespace Darkheim\Application\Vote;

use Darkheim\Infrastructure\Database\Connection;

/**
 * VoteSiteRepository — CRUD for vote sites and top-votes query.
 */
class VoteSiteRepository
{
    protected $muonline;

    public function __construct()
    {
        $this->muonline = Connection::Database('MuOnline');
    }

    /**
     * Returns all votesites ordered by ID, or a single site if $id is given.
     */
    public function findAll(): ?array
    {
        $result = $this->muonline->query_fetch("SELECT * FROM " . Vote_Sites . " ORDER BY votesite_id");
        return is_array($result) ? $result : null;
    }

    public function findById($id): ?array
    {
        if (!check_value($id)) return null;
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Vote_Sites . " WHERE votesite_id = ?", [$id]);
        return is_array($result) ? $result : null;
    }

    public function exists($id): bool
    {
        return $this->findById($id) !== null;
    }

    public function add(string $title, string $link, $reward, $time): bool
    {
        return $this->muonline->query(
            "INSERT INTO " . Vote_Sites . " (votesite_title, votesite_link, votesite_reward, votesite_time) VALUES (?, ?, ?, ?)",
            [$title, $link, $reward, $time]
        );
    }

    public function delete($id): bool
    {
        if (!$this->exists($id)) return false;
        return $this->muonline->query("DELETE FROM " . Vote_Sites . " WHERE votesite_id = ?", [$id]);
    }

    /**
     * Returns the top voted users, optionally limited.
     */
    public function getTopVotes(int $limit = 10): ?array
    {
        $query  = str_replace('{LIMIT}', (string) $limit, "SELECT TOP {LIMIT} * FROM " . Vote_Logs . " GROUP BY user_id ORDER BY COUNT(*) DESC");
        $result = $this->muonline->query_fetch($query);
        return is_array($result) ? $result : null;
    }
}

