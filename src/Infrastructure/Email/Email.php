<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Email;

use Darkheim\Domain\Validator;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Email service — wraps PHPMailer with CMS template support.
 */
class Email
{
    private $_active;
    private $_smtp;
    private $_from;
    private $_name;
    private $_templates;
    private $_templatesPath = __PATH_EMAILS__;

    private $_smtpHost;
    private $_smtpPort;
    private $_smtpUser;
    private $_smtpPass;

    private $_template;
    public $_message {
        set {
            $this->_message = $value;
        }
    }
    private $_to           = [];
    private $_replyTo      = null;
    public $_subject {
        set {
            $this->_subject = $value;
        }
    }
    private $_variables    = [];
    private $_values       = [];

    private $_isCustomTemplate = false;

    protected $mail;

    public function __construct()
    {
        $configs = gconfig('email-templates', true);
        if (!is_array($configs)) throw new \Exception(lang('error_90'));

        $this->_active    = $configs['active'];
        $this->_smtp      = $configs['smtp_active'];
        $this->_from      = $configs['send_from'];
        $this->_name      = $configs['send_name'];
        $this->_smtpHost  = $configs['smtp_host'];
        $this->_smtpPort  = $configs['smtp_port'];
        $this->_smtpUser  = $configs['smtp_user'];
        $this->_smtpPass  = $configs['smtp_pass'];

        if (!is_array($configs['email_templates']['template'])) throw new \Exception();

        $templates = [];
        foreach ($configs['email_templates']['template'] as $template) {
            $templates[$template['filename']] = str_replace("{SERVER_NAME}", config('server_name', true), $template['subject']);
        }

        $this->addVariable("{SERVER_NAME}", config('server_name', true));
        $this->_templates = $templates;
        $this->mail       = new PHPMailer(true);
    }

    public function setFrom($email, $name = "Unknown"): void { $this->_from = $email; $this->_name = $name; }

    public function setTemplate($template): void
    {
        if (!array_key_exists($template, $this->_templates)) throw new \Exception(lang('error_91'));
        $this->_template = $template;
        $this->_subject  = $this->_templates[$template];
    }

    public function addVariable($variable, $value): void
    {
        $this->_variables[] = $variable;
        $this->_values[]    = $value;
    }

    public function addAddress($email): void
    {
        if (!Validator::Email($email)) throw new \Exception(lang('error_92'));
        $this->_to[] = $email;
    }

    public function setReplyTo($email, $name = ''): void
    {
        if (!Validator::Email($email)) throw new \Exception(lang('error_92'));
        $this->_replyTo = [$email, $name];
    }

    private function _loadTemplate(): string
    {
        if (!$this->_template) throw new \Exception(lang('error_93'));
        if ($this->_isCustomTemplate) {
            if (!file_exists($this->_template)) throw new \Exception(lang('error_94'));
            return file_get_contents($this->_template);
        }
        if (!file_exists($this->_templatesPath . $this->_template . '.txt')) throw new \Exception(lang('error_91'));
        return file_get_contents($this->_templatesPath . $this->_template . '.txt');
    }

    private function _prepareTemplate(): string
    {
        return str_replace($this->_variables, $this->_values, $this->_loadTemplate());
    }

    public function send(): bool
    {
        if (!$this->_active) throw new \Exception(lang('error_48', true));
        if (!$this->_message && !$this->_template) throw new \Exception(lang('error_95'));
        if (!is_array($this->_to)) throw new \Exception(lang('error_96'));

        if ($this->_smtp) {
            $this->mail->IsSMTP();
            $this->mail->SMTPAuth = true;
            $this->mail->Host     = $this->_smtpHost;
            $this->mail->Port     = $this->_smtpPort;
            $this->mail->Username = $this->_smtpUser;
            $this->mail->Password = $this->_smtpPass;
        }

        $this->mail->SetFrom($this->_from, $this->_name);

        foreach ($this->_to as $address) {
            $this->mail->AddAddress($address);
        }

        if (!$this->_subject) throw new \Exception(lang('error_97'));
        $this->mail->Subject = $this->_subject;

        if (!$this->_message) {
            $this->mail->MsgHTML($this->_prepareTemplate());
        } else {
            $this->mail->MsgHTML($this->_message);
        }

        if (is_array($this->_replyTo)) {
            $this->mail->addReplyTo($this->_replyTo[0], $this->_replyTo[1]);
        }

        return $this->mail->Send();
    }
}

