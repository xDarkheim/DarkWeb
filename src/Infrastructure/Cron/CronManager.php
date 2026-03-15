<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Cron;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Domain\Validator;

/**
 * Cron job management — CRUD and status control for scheduled tasks.
 */
class CronManager
{
    private $_api = 'cron.php';

    protected $_id;
    public $_name {
        set {
            $this->_name = $value;
        }
    }
    protected $_desc;
    protected $_file;
    public $_interval {
        set {
            $this->_interval = $value;
        }
    }

    public $_commonIntervals = [
        60      => '1 minute',
        300     => '5 minutes',
        600     => '10 minutes',
        900     => '15 minutes',
        1800    => '30 minutes',
        3600    => '1 hour',
        7200    => '2 hours',
        14400   => '4 hours',
        21600   => '6 hours',
        43200   => '12 hours',
        86400   => '1 day ',
        604800  => '7 days',
        1296000 => '15 days',
        2592000 => '1 month',
        7776000 => '3 months',
        15552000 => '6 months',
        31104000 => '1 year',
    ] {
        get {
            return $this->_commonIntervals;
        }
    }

    protected $muonline;

    public function __construct()
    {
        $this->muonline = Connection::Database('MuOnline');
    }

    public function setId($id): void
    {
        if (!Validator::UnsignedNumber($id)) throw new \Exception(lang('error_49'));
        $this->_id = $id;
    }

    public function setDescription($desc): void { $this->_desc = $desc; }

    public function setFile($file): void
    {
        if (!$this->_cronFileExists($file)) throw new \Exception(lang('error_50'));
        $this->_file = $file;
    }

    public function getCronList()
    {
        $result = $this->muonline->query_fetch("SELECT * FROM " . Cron . " ORDER BY cron_id");
        if (!is_array($result)) return;
        return $result;
    }

    public function enableCron(): void  { $this->_setCronStatus(1); }
    public function disableCron(): void { $this->_setCronStatus(0); }

    public function resetCronLastRun(): bool
    {
        if (!check_value($this->_id)) return false;
        $result = $this->muonline->query("UPDATE " . Cron . " SET cron_last_run = NULL WHERE cron_id = ?", array($this->_id));
        if (!$result) throw new \Exception($this->muonline->error);
        return true;
    }

    public function deleteCron(): bool
    {
        if (!check_value($this->_id)) return false;
        $result = $this->muonline->query("DELETE FROM " . Cron . " WHERE cron_id = ?", array($this->_id));
        if (!$result) throw new \Exception($this->muonline->error);
        return true;
    }

    public function getCronApiUrl($id = null): string
    {
        if (check_value($id)) return __PATH_API__ . $this->_api . '?key=' . config('cron_api_key', true) . '&id=' . $id;
        return __PATH_API__ . $this->_api . '?key=' . config('cron_api_key', true);
    }

    public function addCron(): bool
    {
        if (!check_value($this->_name)) throw new \Exception(lang('error_106'));
        if (!check_value($this->_file)) throw new \Exception(lang('error_106'));
        if (!check_value($this->_interval)) throw new \Exception(lang('error_106'));
        if ($this->_cronAlreadyExists()) throw new \Exception(lang('error_107'));

        $data   = [$this->_name, $this->_file, $this->_interval, 1, 0, $this->_cronFileMd5($this->_file)];
        $result = $this->muonline->query("INSERT INTO " . Cron . " (cron_name, cron_file_run, cron_run_time, cron_status, cron_protected, cron_file_md5) VALUES (?, ?, ?, ?, ?, ?)", $data);
        if (!$result) throw new \Exception($this->muonline->error);
        return true;
    }

    public function enableAll(): bool
    {
        $result = $this->muonline->query("UPDATE " . Cron . " SET cron_status = 1");
        if (!$result) throw new \Exception($this->muonline->error);
        return true;
    }

    public function disableAll(): bool
    {
        $result = $this->muonline->query("UPDATE " . Cron . " SET cron_status = 0");
        if (!$result) throw new \Exception($this->muonline->error);
        return true;
    }

    public function resetAllLastRun(): bool
    {
        $result = $this->muonline->query("UPDATE " . Cron . " SET cron_last_run = NULL");
        if (!$result) throw new \Exception($this->muonline->error);
        return true;
    }

    public function listCronFiles($selected = ''): string
    {
        $dir    = opendir(__PATH_CRON__);
        $return = [];
        while (($file = readdir($dir)) !== false) {
            if (filetype(__PATH_CRON__ . $file) == "file" && $file != ".htaccess" && $file != "cron.php") {
                if (check_value($selected) && $selected == $file) {
                    $return[] = "<option value=\"$file\" selected=\"selected\">$file</option>";
                } else {
                    $return[] = "<option value=\"$file\">$file</option>";
                }
            }
        }
        closedir($dir);
        return implode('', $return);
    }

    protected function _cronFileExists($file): bool
    {
        return file_exists(__PATH_CRON__ . $file);
    }

    protected function _setCronStatus($status = 1): bool
    {
        if (!check_value($this->_id)) return false;
        $result = $this->muonline->query("UPDATE " . Cron . " SET cron_status = ? WHERE cron_id = ?", array($status, $this->_id));
        if (!$result) throw new \Exception($this->muonline->error);
        return true;
    }

    protected function _cronAlreadyExists(): bool
    {
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Cron . " WHERE cron_file_run = ?", array($this->_file));
        return is_array($result);
    }

    protected function _cronFileMd5($file): string
    {
        return md5_file(__PATH_CRON__ . $file) ?: '';
    }
}

