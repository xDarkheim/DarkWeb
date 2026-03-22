<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Application\Vote\VoteSiteRepository;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ModulesManagerController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $admincpUrl = new AdmincpUrlGenerator();

        $cmsModules = [
            '_global' => [
                ['News', 'news'], ['Login', 'login'], ['Register', 'register'],
                ['Downloads', 'downloads'], ['Donation', 'donation'], ['PayPal', 'paypal'],
                ['Rankings', 'rankings'], ['Castle Siege', 'castlesiege'], ['Email System', 'email'],
                ['Profiles', 'profiles'], ['Contact Us', 'contact'], ['Forgot Password', 'forgotpassword'],
            ],
            '_usercp' => [
                ['Add Stats', 'addstats'], ['Clear PK', 'clearpk'], ['Clear Skill-Tree', 'clearskilltree'],
                ['My Account', 'myaccount'], ['Change Password', 'mypassword'], ['Change Email', 'myemail'],
                ['Character Reset', 'reset'], ['Reset Stats', 'resetstats'], ['Unstick Character', 'unstick'],
                ['Vote and Reward', 'vote'], ['Buy Zen', 'buyzen'],
            ],
        ];

        $configKey      = null;
        $configFilePath = null;
        if (isset($_GET['config'])) {
            $usercpModules = ['addstats', 'buyzen', 'clearpk', 'clearskilltree', 'myaccount', 'myemail', 'mypassword', 'reset', 'resetstats', 'unstick', 'vote'];
            $configKey     = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $_GET['config']));

            $this->handleConfigActions($configKey);
            $this->handleSimpleConfigSave($configKey);

            $moduleConfigName = $this->moduleConfigNameFromKey($configKey);
            \Darkheim\Infrastructure\Bootstrap\BootstrapContext::loadModuleConfig($moduleConfigName);

            $subDir   = in_array($configKey, $usercpModules, true) ? 'usercp/' : '';
            $filePath = __PATH_VIEWS__ . 'admincp/mconfig/' . $subDir . $configKey . '.php';
            if (is_file($filePath)) {
                $configFilePath = $filePath;
            } else {
                \Darkheim\Application\View\MessageRenderer::toast('error', 'Invalid module.');
            }
        }

        $mconfigData = $this->prepareMconfigData($configKey);
        $globalModules = $this->buildModuleLinks($cmsModules['_global'], $admincpUrl);
        $usercpModules = $this->buildModuleLinks($cmsModules['_usercp'], $admincpUrl);

        $this->view->render('admincp/modulesmanager', [
            'globalModules'          => $globalModules,
            'usercpModules'          => $usercpModules,
            'selectedConfigKey'      => $configKey,
            'selectedConfigFilePath' => $configFilePath,
            'downloadsConfigUrl'     => $admincpUrl->base('modules_manager&config=downloads'),
            'downloadsDeleteUrlBase' => $admincpUrl->base('modules_manager&config=downloads&deletelink='),
            'voteConfigUrl'          => $admincpUrl->base('modules_manager&config=vote'),
            'voteDeleteSiteUrlBase'  => $admincpUrl->base('modules_manager&config=vote&deletesite='),
            ...$mconfigData,
        ]);
    }

    /**
     * @param array<int,array{0:string,1:string}> $modules
     * @return array<int,array{label:string,key:string,url:string}>
     */
    private function buildModuleLinks(array $modules, AdmincpUrlGenerator $admincpUrl): array
    {
        $links = [];
        foreach ($modules as $module) {
            $key = (string) ($module[1] ?? '');
            $links[] = [
                'label' => (string) ($module[0] ?? ''),
                'key'   => $key,
                'url'   => $admincpUrl->base('modules_manager&config=' . $key),
            ];
        }

        return $links;
    }

    /**
     * @return array<string,mixed>
     * @throws \Exception
     */
    private function prepareMconfigData(?string $configKey): array
    {
        $data = [];

        if ($configKey === 'downloads') {
            $data['downloadsList'] = new DownloadLinkService()->all();
        }

        if ($configKey === 'vote') {
            $voteSiteRepository = new VoteSiteRepository();

            $data['voteSiteList'] = $voteSiteRepository->findAll();
            $this->addCreditConfigSelect($data, 'voteCreditConfigSelect', 'setting_3', 'credit_config');
        }

        if ($configKey === 'paypal') {
            $this->addCreditConfigSelect($data, 'paypalCreditConfigSelect', 'setting_10', 'credit_config');
        }

        if ($configKey === 'addstats') {
            $this->addCreditConfigSelect($data, 'addstatsCreditConfigSelect', 'setting_3', 'credit_config');
        }

        if ($configKey === 'buyzen') {
            $this->addCreditConfigSelect($data, 'buyzenCreditConfigSelect', 'setting_4', 'credit_config');
        }

        if ($configKey === 'clearpk') {
            $this->addCreditConfigSelect($data, 'clearpkCreditConfigSelect', 'setting_3', 'credit_config');
        }

        if ($configKey === 'clearskilltree') {
            $this->addCreditConfigSelect($data, 'clearskilltreeCreditConfigSelect', 'setting_3', 'credit_config');
        }

        if ($configKey === 'reset') {
            $this->addCreditConfigSelect($data, 'resetCostCreditConfigSelect', 'setting_3', 'credit_config');
            $this->addCreditConfigSelect($data, 'resetRewardCreditConfigSelect', 'setting_13', 'credit_reward_config');
        }

        if ($configKey === 'resetstats') {
            $this->addCreditConfigSelect($data, 'resetstatsCreditConfigSelect', 'setting_3', 'credit_config');
        }

        if ($configKey === 'unstick') {
            $this->addCreditConfigSelect($data, 'unstickCreditConfigSelect', 'setting_3', 'credit_config');
        }

        if ($configKey === 'email') {
            $emailConfigs         = BootstrapContext::configProvider()?->globalXml('email-templates');
            $data['emailConfigs'] = is_array($emailConfigs) ? $emailConfigs : null;
        }

        if ($configKey === 'rankings') {
            $xmlPath = __PATH_MODULE_CONFIGS__ . 'rankings.xml';
            $xmlRaw  = file_get_contents($xmlPath);
            if ($xmlRaw !== false) {
                $data['rankingsModuleConfig'] = simplexml_load_string($xmlRaw);
            }
        }

        if ($configKey === 'castlesiege') {
            try {
                $cfgRaw = file_get_contents(__PATH_CONFIGS__ . 'castle-siege.json');
                if ($cfgRaw !== false) {
                    $cfg = json_decode($cfgRaw, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($cfg)) {
                        $data['castleSiegeConfig'] = $cfg;
                    }
                }
            } catch (\JsonException $e) {
                \Darkheim\Application\View\MessageRenderer::toast('error', 'Error loading Castle Siege config: ' . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function addCreditConfigSelect(array &$data, string $viewKey, string $settingName, string $mconfigKey): void
    {
        $creditSystem = new CreditSystem();
        $rawDefault = \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue($mconfigKey);
        if (is_int($rawDefault)) {
            $default = $rawDefault;
        } elseif (is_numeric($rawDefault)) {
            $default = (int) $rawDefault;
        } else {
            $default = 0;
        }

        $data[$viewKey] = $creditSystem->buildSelectInput($settingName, $default, 'form-control');
    }

    private function handleConfigActions(string $configKey): void
    {
        if ($configKey === 'downloads') {
            $this->handleDownloadsActions();
            return;
        }

        if ($configKey === 'vote') {
            $this->handleVoteActions();
            return;
        }

        if ($configKey === 'castlesiege') {
            $this->handleCastleSiegeActions();
            return;
        }
    }

    private function handleDownloadsActions(): void
    {
        $downloads = new DownloadLinkService();

        if (isset($_POST['downloads_add_submit'])) {
            $action = $downloads->add(
                (string) ($_POST['downloads_add_title'] ?? ''),
                (string) ($_POST['downloads_add_link'] ?? ''),
                (string) ($_POST['downloads_add_desc'] ?? ''),
                (string) ($_POST['downloads_add_size'] ?? ''),
                (string) ($_POST['downloads_add_type'] ?? ''),
            );
            $action ? \Darkheim\Application\View\MessageRenderer::toast('success', 'Your download link has been successfully added!') : \Darkheim\Application\View\MessageRenderer::toast('error', 'There was an error adding the download link.');
        }

        if (isset($_POST['downloads_edit_submit'])) {
            $action = $downloads->edit(
                (string) ($_POST['downloads_edit_id'] ?? ''),
                (string) ($_POST['downloads_edit_title'] ?? ''),
                (string) ($_POST['downloads_edit_link'] ?? ''),
                (string) ($_POST['downloads_edit_desc'] ?? ''),
                (string) ($_POST['downloads_edit_size'] ?? ''),
                (string) ($_POST['downloads_edit_type'] ?? ''),
            );
            $action ? \Darkheim\Application\View\MessageRenderer::toast('success', 'Your download link has been successfully updated!') : \Darkheim\Application\View\MessageRenderer::toast('error', 'There was an error updating the download link.');
        }

        if (isset($_REQUEST['deletelink'])) {
            $action = $downloads->delete((string) $_REQUEST['deletelink']);
            $action ? \Darkheim\Application\View\MessageRenderer::toast('success', 'Your download link has been successfully deleted!') : \Darkheim\Application\View\MessageRenderer::toast('error', 'There was an error deleting the download link.');
        }
    }

    private function handleVoteActions(): void
    {
        $voteSiteRepository = new VoteSiteRepository();

        if (isset($_POST['votesite_add_submit'])) {
            $add = $voteSiteRepository->add(
                (string) ($_POST['votesite_add_title'] ?? ''),
                (string) ($_POST['votesite_add_link'] ?? ''),
                (string) ($_POST['votesite_add_reward'] ?? ''),
                (string) ($_POST['votesite_add_time'] ?? ''),
            );
            $add ? \Darkheim\Application\View\MessageRenderer::toast('success', 'Votesite successfully added.') : \Darkheim\Application\View\MessageRenderer::toast('error', 'There has been an error while adding the topsite.');
        }

        if (isset($_REQUEST['deletesite'])) {
            $delete = $voteSiteRepository->delete((string) $_REQUEST['deletesite']);
            $delete ? \Darkheim\Application\View\MessageRenderer::toast('success', 'Votesite successfully deleted.') : \Darkheim\Application\View\MessageRenderer::toast('error', 'There has been an error while deleting the topsite.');
        }
    }

    private function handleCastleSiegeActions(): void
    {
        if (! isset($_POST['submit_changes'])) {
            return;
        }

        try {
            $cfgFile = __PATH_CONFIGS__ . 'castle-siege.json';
            if (! is_writable($cfgFile)) {
                throw new \RuntimeException('The configuration file is not writable.');
            }

            $raw = file_get_contents($cfgFile);
            $cfg = json_decode((string) $raw, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($cfg)) {
                throw new \RuntimeException('Error loading config file.');
            }

            foreach (range(1, 14) as $i) {
                $key = 'setting_' . $i;
                if (! isset($_POST[$key]) || ! Validator::UnsignedNumber($_POST[$key]) || ! in_array($_POST[$key], ['0', '1', 0, 1], true)) {
                    throw new \RuntimeException('Submitted setting is not valid (' . $key . ')');
                }
            }

            $cfg['active']                     = $_POST['setting_1'];
            $cfg['hide_idle']                  = $_POST['setting_2'];
            $cfg['live_data']                  = $_POST['setting_3'];
            $cfg['show_castle_owner']          = $_POST['setting_4'];
            $cfg['show_castle_owner_alliance'] = $_POST['setting_5'];
            $cfg['show_battle_countdown']      = $_POST['setting_6'];
            $cfg['show_castle_information']    = $_POST['setting_7'];
            $cfg['show_current_stage']         = $_POST['setting_8'];
            $cfg['show_next_stage']            = $_POST['setting_9'];
            $cfg['show_battle_duration']       = $_POST['setting_10'];
            $cfg['show_registered_guilds']     = $_POST['setting_11'];
            $cfg['show_schedule']              = $_POST['setting_12'];
            $cfg['schedule_date_format']       = (string) ($_POST['setting_13'] ?? '');
            $cfg['show_widget']                = $_POST['setting_14'];

            $stageCount = is_array($cfg['stages'] ?? null) ? count($cfg['stages']) : 0;
            foreach (['setting_stage_startday', 'setting_stage_starttime', 'setting_stage_endday', 'setting_stage_endtime'] as $arrKey) {
                if (! isset($_POST[$arrKey]) || ! is_array($_POST[$arrKey]) || count($_POST[$arrKey]) !== $stageCount) {
                    throw new \RuntimeException('Schedule stages settings array size is not valid.');
                }
            }

            foreach ($_POST['setting_stage_startday'] as $i => $v) {
                $cfg['stages'][$i]['start_day'] = $v;
            }
            foreach ($_POST['setting_stage_starttime'] as $i => $v) {
                $cfg['stages'][$i]['start_time'] = $v;
            }
            foreach ($_POST['setting_stage_endday'] as $i => $v) {
                $cfg['stages'][$i]['end_day'] = $v;
            }
            foreach ($_POST['setting_stage_endtime'] as $i => $v) {
                $cfg['stages'][$i]['end_time'] = $v;
            }

            $encoded = json_encode($cfg, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            if (file_put_contents($cfgFile, $encoded) === false) {
                throw new \RuntimeException('There has been an error while saving changes.');
            }

            \Darkheim\Application\View\MessageRenderer::toast('success', 'Settings successfully saved.');
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    private function handleSimpleConfigSave(?string $configKey): void
    {
        if (! isset($_POST['submit_changes']) || ! is_string($configKey) || $configKey === '') {
            return;
        }

        $simpleMap = [
            'contact' => [
                'xml'    => 'contact.xml',
                'fields' => ['setting_1' => 'active', 'setting_2' => 'subject', 'setting_3' => 'sendto'],
            ],
            'donation' => [
                'xml'     => 'donation.xml',
                'fields'  => ['setting_1' => 'active'],
                'success' => '[Donation] Settings successfully saved.',
                'error'   => '[Donation] There has been an error while saving changes.',
            ],
            'forgotpassword' => [
                'xml'    => 'forgot-password.xml',
                'fields' => ['setting_1' => 'active'],
            ],
            'login' => [
                'xml'    => 'login.xml',
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'enable_session_timeout',
                    'setting_3' => 'session_timeout',
                    'setting_4' => 'max_login_attempts',
                    'setting_5' => 'failed_login_timeout',
                ],
            ],
            'news' => [
                'xml'    => 'news.xml',
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'news_expanded',
                    'setting_3' => 'news_list_limit',
                    'setting_6' => 'news_short',
                    'setting_7' => 'news_short_char_limit',
                ],
            ],
            'paypal' => [
                'xml'    => 'donation-paypal.xml',
                'fields' => [
                    'setting_2'  => 'active',
                    'setting_3'  => 'paypal_enable_sandbox',
                    'setting_4'  => 'paypal_email',
                    'setting_5'  => 'paypal_title',
                    'setting_6'  => 'paypal_currency',
                    'setting_7'  => 'paypal_return_url',
                    'setting_8'  => 'paypal_notify_url',
                    'setting_9'  => 'paypal_conversion_rate',
                    'setting_10' => 'credit_config',
                ],
                'success' => '[PayPal] Settings successfully saved.',
                'error'   => '[PayPal] There has been an error while saving changes.',
            ],
            'profiles' => [
                'xml'    => 'profiles.xml',
                'fields' => ['setting_1' => 'active', 'setting_2' => 'encode'],
            ],
            'email' => [
                'xml'    => 'email-templates.xml',
                'base'   => __PATH_CONFIGS__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'send_from',
                    'setting_3' => 'send_name',
                    'setting_4' => 'smtp_active',
                    'setting_5' => 'smtp_host',
                    'setting_6' => 'smtp_port',
                    'setting_7' => 'smtp_user',
                    'setting_8' => 'smtp_pass',
                ],
            ],
            'rankings' => [
                'xml'    => 'rankings.xml',
                'fields' => [
                    'setting_1'  => 'active',
                    'setting_2'  => 'rankings_results',
                    'setting_3'  => 'rankings_show_date',
                    'setting_4'  => 'rankings_show_default',
                    'setting_5'  => 'rankings_show_place_number',
                    'setting_6'  => 'rankings_enable_level',
                    'setting_7'  => 'rankings_enable_resets',
                    'setting_8'  => 'rankings_enable_pk',
                    'setting_9'  => 'rankings_enable_gr',
                    'setting_10' => 'rankings_enable_online',
                    'setting_11' => 'rankings_enable_guilds',
                    'setting_12' => 'rankings_enable_master',
                    'setting_14' => 'rankings_enable_gens',
                    'setting_15' => 'rankings_enable_votes',
                    'setting_16' => 'rankings_excluded_characters',
                    'setting_17' => 'combine_level_masterlevel',
                    'setting_18' => 'show_country_flags',
                    'setting_19' => 'show_location',
                    'setting_20' => 'show_online_status',
                    'setting_21' => 'guild_score_formula',
                    'setting_22' => 'guild_score_multiplier',
                    'setting_23' => 'rankings_excluded_guilds',
                    'setting_24' => 'rankings_class_filter',
                ],
            ],
            'clearpk' => [
                'xml'    => 'clear-pk.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'zen_cost',
                    'setting_3' => 'credit_config',
                    'setting_4' => 'credit_cost',
                ],
            ],
            'buyzen' => [
                'xml'    => 'buy-zen.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'max_zen',
                    'setting_3' => 'exchange_ratio',
                    'setting_4' => 'credit_config',
                    'setting_5' => 'increment_rate',
                ],
            ],
            'myaccount' => [
                'xml'    => 'my-account.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                ],
            ],
            'myemail' => [
                'xml'    => 'my-email.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'require_verification',
                ],
            ],
            'mypassword' => [
                'xml'    => 'my-password.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'change_password_email_verification',
                    'setting_3' => 'change_password_request_timeout',
                ],
            ],
            'resetstats' => [
                'xml'    => 'reset-stats.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'zen_cost',
                    'setting_3' => 'credit_config',
                    'setting_4' => 'credit_cost',
                ],
            ],
            'unstick' => [
                'xml'    => 'unstick.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'zen_cost',
                    'setting_3' => 'credit_config',
                    'setting_4' => 'credit_cost',
                ],
            ],
            'register' => [
                'xml'    => 'register.xml',
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'register_enable_recaptcha',
                    'setting_3' => 'register_recaptcha_site_key',
                    'setting_4' => 'register_recaptcha_secret_key',
                    'setting_5' => 'verify_email',
                    'setting_6' => 'send_welcome_email',
                    'setting_7' => 'verification_timelimit',
                    'setting_8' => 'automatic_login',
                ],
            ],
            'downloads' => [
                'xml'    => 'downloads.xml',
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'show_client_downloads',
                    'setting_3' => 'show_patch_downloads',
                    'setting_4' => 'show_tool_downloads',
                ],
            ],
            'vote' => [
                'xml'    => 'vote.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'vote_save_logs',
                    'setting_3' => 'credit_config',
                ],
            ],
            'addstats' => [
                'xml'    => 'add-stats.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'zen_cost',
                    'setting_3' => 'credit_config',
                    'setting_4' => 'credit_cost',
                    'setting_5' => 'required_level',
                    'setting_6' => 'required_master_level',
                    'setting_7' => 'max_stats',
                    'setting_8' => 'minimum_limit',
                ],
            ],
            'clearskilltree' => [
                'xml'    => 'clear-skill-tree.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1' => 'active',
                    'setting_2' => 'zen_cost',
                    'setting_3' => 'credit_config',
                    'setting_4' => 'credit_cost',
                    'setting_5' => 'required_level',
                    'setting_6' => 'required_master_level',
                ],
            ],
            'reset' => [
                'xml'    => 'reset.xml',
                'base'   => __PATH_MODULE_CONFIGS_USERCP__,
                'fields' => [
                    'setting_1'  => 'active',
                    'setting_2'  => 'zen_cost',
                    'setting_3'  => 'credit_config',
                    'setting_4'  => 'credit_cost',
                    'setting_5'  => 'required_level',
                    'setting_6'  => 'maximum_resets',
                    'setting_7'  => 'keep_stats',
                    'setting_8'  => 'points_reward',
                    'setting_9'  => 'multiply_points_by_resets',
                    'setting_10' => 'clear_inventory',
                    'setting_11' => 'revert_class_evolution',
                    'setting_12' => 'credit_reward',
                    'setting_13' => 'credit_reward_config',
                ],
            ],
        ];

        if (! isset($simpleMap[$configKey])) {
            return;
        }

        foreach ($_POST as $setting) {
            if (! Validator::hasValue($setting)) {
                \Darkheim\Application\View\MessageRenderer::toast('error', 'Missing data (complete all fields).');
                return;
            }
        }

        if (in_array($configKey, ['addstats', 'clearskilltree'], true) && isset($_POST['setting_5']) && (int) $_POST['setting_5'] > 400) {
            \Darkheim\Application\View\MessageRenderer::toast('error', 'The required level setting can have a maximum value of 400.');
            return;
        }

        $map      = $simpleMap[$configKey];
        $basePath = $map['base'] ?? __PATH_MODULE_CONFIGS__;
        $xmlPath  = $basePath . $map['xml'];
        $xml      = simplexml_load_string((string) file_get_contents($xmlPath));
        if (! $xml) {
            \Darkheim\Application\View\MessageRenderer::toast('error', 'There has been an error while loading module settings.');
            return;
        }

        foreach ($map['fields'] as $postKey => $xmlField) {
            $xml->{$xmlField} = $_POST[$postKey] ?? '';
        }

        $saved = $xml->asXML($xmlPath);
        if ($saved) {
            \Darkheim\Application\View\MessageRenderer::toast('success', $map['success'] ?? 'Settings successfully saved.');
        } else {
            \Darkheim\Application\View\MessageRenderer::toast('error', $map['error'] ?? 'There has been an error while saving changes.');
        }
    }

    private function moduleConfigNameFromKey(string $configKey): string
    {
        $map = [
            'buyzen'         => 'buy-zen',
            'clearpk'        => 'clear-pk',
            'clearskilltree' => 'clear-skill-tree',
            'forgotpassword' => 'forgot-password',
            'myaccount'      => 'my-account',
            'myemail'        => 'my-email',
            'mypassword'     => 'my-password',
            'paypal'         => 'donation-paypal',
            'addstats'       => 'add-stats',
            'resetstats'     => 'reset-stats',
        ];

        return $map[$configKey] ?? $configKey;
    }
}
