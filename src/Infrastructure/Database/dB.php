<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Database;

use PDO;
use PDOException;
use PDOStatement;

/**
 * PDO database abstraction layer — query, fetch, error logging.
 */
class dB
{
    public ?string $error = null;
    public ?bool $ok      = null;
    public bool $dead     = false;

    private bool $_enableErrorLogs = true;

    protected PDO $db;

    public function __construct(string $SQLHOST, string $SQLPORT, string $SQLDB, string $SQLUSER, string $SQLPWD)
    {
        try {
            $pdo_connect = 'dblib:host=' . $SQLHOST . ':' . $SQLPORT . ';dbname=' . $SQLDB;
            $this->db = new PDO($pdo_connect, $SQLUSER, $SQLPWD);

            if (!$this->db->getAttribute(PDO::ATTR_EMULATE_PREPARES)) {
                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            }
        } catch (PDOException $e) {
            $this->dead  = true;
            $this->error = 'PDOException: ' . $e->getMessage();
        }
    }

    public function query(string $sql, mixed $array = []): bool
    {
        $params = $this->normalizeParams($array);
        $query  = $this->db->prepare($sql);

        if (!$query) {
            $this->error = $this->trow_error();
            return false;
        }

        if ($query->execute($params)) {
            $query->closeCursor();
            return true;
        }

        $this->error = $this->trow_error($query);
        return false;
    }

    public function query_fetch(string $sql, mixed $array = []): array|null|false
    {
        $params = $this->normalizeParams($array);
        $query  = $this->db->prepare($sql);

        if (!$query) {
            $this->error = $this->trow_error();
            return false;
        }

        if ($query->execute($params)) {
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $query->closeCursor();
            return (check_value($result)) ? $result : null;
        }

        $this->error = $this->trow_error($query);
        return false;
    }

    public function query_fetch_single(string $sql, mixed $array = []): array|null
    {
        $result = $this->query_fetch($sql, $array);
        return $result[0] ?? null;
    }

    private function normalizeParams(mixed $array): array
    {
        if (is_array($array)) {
            return $array;
        }
        return ($array === '') ? [] : [$array];
    }

    private function trow_error(PDOStatement|false $state = false): string
    {
        $error        = $state ? $state->errorInfo() : $this->db->errorInfo();
        $errorMessage = '[' . date('Y/m/d h:i:s') . '] [SQL ' . $error[0] . '] [' . $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) . ' ' . $error[1] . '] > ' . $error[2];

        if ($this->_enableErrorLogs) {
            // noinspection ForgottenDebugOutputInspection — intentional persistent error log, not debug output
            error_log($errorMessage . "\r\n", 3, DARKHEIM_DATABASE_ERRORLOG);
        }

        return $errorMessage;
    }
}

