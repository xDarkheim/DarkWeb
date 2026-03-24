<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\Settings;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use Darkheim\Application\Admincp\Support\DownloadLinkService;
use Darkheim\Application\Admincp\Support\ModuleConfigCatalog;
use Darkheim\Application\Admincp\Support\XmlModuleConfigSaver;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Application\Vote\VoteSiteRepository;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Runtime\Contracts\PostStore;
use Darkheim\Infrastructure\Runtime\Contracts\QueryStore;
use Darkheim\Infrastructure\Runtime\Contracts\RequestStore;
use Darkheim\Infrastructure\Runtime\Native\NativePostStore;
use Darkheim\Infrastructure\Runtime\Native\NativeQueryStore;
use Darkheim\Infrastructure\Runtime\Native\NativeRequestStore;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ModulesManagerController
{
    private ViewRenderer $view;
    private QueryStore $query;
    private PostStore $post;
    private RequestStore $request;
    private ModuleConfigCatalog $moduleConfigCatalog;
    private XmlModuleConfigSaver $xmlModuleConfigSaver;

    public function __construct(
        ?ViewRenderer $view = null,
        ?QueryStore $query = null,
        ?PostStore $post = null,
        ?RequestStore $request = null,
        ?ModuleConfigCatalog $moduleConfigCatalog = null,
        ?XmlModuleConfigSaver $xmlModuleConfigSaver = null,
    ) {
        $this->view                 = $view                 ?? new ViewRenderer();
        $this->query                = $query                ?? new NativeQueryStore();
        $this->post                 = $post                 ?? new NativePostStore();
        $this->request              = $request              ?? new NativeRequestStore();
        $this->moduleConfigCatalog  = $moduleConfigCatalog  ?? new ModuleConfigCatalog();
        $this->xmlModuleConfigSaver = $xmlModuleConfigSaver ?? new XmlModuleConfigSaver();
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
        if ($this->query->has('config')) {
            $usercpModules = ['addstats', 'buyzen', 'clearpk', 'clearskilltree', 'myaccount', 'myemail', 'mypassword', 'reset', 'resetstats', 'unstick', 'vote'];
            $configKey     = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $this->query->get('config', '')));

            $this->handleConfigActions($configKey);
            $this->handleSimpleConfigSave($configKey);

            $moduleConfigName = $this->moduleConfigCatalog->moduleConfigNameFromKey($configKey);
            BootstrapContext::loadModuleConfig($moduleConfigName);

            $subDir   = in_array($configKey, $usercpModules, true) ? 'usercp/' : '';
            $filePath = __PATH_VIEWS__ . 'admincp/mconfig/' . $subDir . $configKey . '.php';
            if (is_file($filePath)) {
                $configFilePath = $filePath;
            } else {
                MessageRenderer::toast('error', 'Invalid module.');
            }
        }

        $mconfigData              = $this->prepareMconfigData($configKey);
        $globalModules            = $this->buildModuleLinks($cmsModules['_global'], $admincpUrl);
        $usercpModules            = $this->buildModuleLinks($cmsModules['_usercp'], $admincpUrl);
        $selectedConfigFormAction = is_string($configKey) && $configKey !== ''
            ? $admincpUrl->base('modules_manager&config=' . $configKey)
            : '';

        $this->view->render('admincp/modulesmanager', [
            'globalModules'            => $globalModules,
            'usercpModules'            => $usercpModules,
            'selectedConfigKey'        => $configKey,
            'selectedConfigFilePath'   => $configFilePath,
            'selectedConfigFormAction' => $selectedConfigFormAction,
            'downloadsConfigUrl'       => $admincpUrl->base('modules_manager&config=downloads'),
            'downloadsDeleteUrlBase'   => $admincpUrl->base('modules_manager&config=downloads&deletelink='),
            'voteConfigUrl'            => $admincpUrl->base('modules_manager&config=vote'),
            'voteDeleteSiteUrlBase'    => $admincpUrl->base('modules_manager&config=vote&deletesite='),
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
            $key     = (string) $module[1];
            $links[] = [
                'label' => (string) $module[0],
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
                MessageRenderer::toast('error', 'Error loading Castle Siege config: ' . $e->getMessage());
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
        $rawDefault   = BootstrapContext::moduleValue($mconfigKey);
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

        if ($this->post->has('downloads_add_submit')) {
            $action = $downloads->add(
                (string) $this->post->get('downloads_add_title', ''),
                (string) $this->post->get('downloads_add_link', ''),
                (string) $this->post->get('downloads_add_desc', ''),
                (string) $this->post->get('downloads_add_size', ''),
                (string) $this->post->get('downloads_add_type', ''),
            );
            $action ? MessageRenderer::toast('success', 'Your download link has been successfully added!') : MessageRenderer::toast('error', 'There was an error adding the download link.');
        }

        if ($this->post->has('downloads_edit_submit')) {
            $action = $downloads->edit(
                (string) $this->post->get('downloads_edit_id', ''),
                (string) $this->post->get('downloads_edit_title', ''),
                (string) $this->post->get('downloads_edit_link', ''),
                (string) $this->post->get('downloads_edit_desc', ''),
                (string) $this->post->get('downloads_edit_size', ''),
                (string) $this->post->get('downloads_edit_type', ''),
            );
            $action ? MessageRenderer::toast('success', 'Your download link has been successfully updated!') : MessageRenderer::toast('error', 'There was an error updating the download link.');
        }

        if ($this->request->has('deletelink')) {
            $action = $downloads->delete((string) $this->request->get('deletelink', ''));
            $action ? MessageRenderer::toast('success', 'Your download link has been successfully deleted!') : MessageRenderer::toast('error', 'There was an error deleting the download link.');
        }
    }

    private function handleVoteActions(): void
    {
        $voteSiteRepository = new VoteSiteRepository();

        if ($this->post->has('votesite_add_submit')) {
            $add = $voteSiteRepository->add(
                (string) $this->post->get('votesite_add_title', ''),
                (string) $this->post->get('votesite_add_link', ''),
                (string) $this->post->get('votesite_add_reward', ''),
                (string) $this->post->get('votesite_add_time', ''),
            );
            $add ? MessageRenderer::toast('success', 'Votesite successfully added.') : MessageRenderer::toast('error', 'There has been an error while adding the topsite.');
        }

        if ($this->request->has('deletesite')) {
            $delete = $voteSiteRepository->delete((string) $this->request->get('deletesite', ''));
            $delete ? MessageRenderer::toast('success', 'Votesite successfully deleted.') : MessageRenderer::toast('error', 'There has been an error while deleting the topsite.');
        }
    }

    private function handleCastleSiegeActions(): void
    {
        if (! $this->post->has('submit_changes')) {
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
                $key   = 'setting_' . $i;
                $value = $this->post->get($key);
                if (! $this->post->has($key) || ! Validator::UnsignedNumber($value) || ! in_array($value, ['0', '1', 0, 1], true)) {
                    throw new \RuntimeException('Submitted setting is not valid (' . $key . ')');
                }
            }

            $cfg['active']                     = $this->post->get('setting_1');
            $cfg['hide_idle']                  = $this->post->get('setting_2');
            $cfg['live_data']                  = $this->post->get('setting_3');
            $cfg['show_castle_owner']          = $this->post->get('setting_4');
            $cfg['show_castle_owner_alliance'] = $this->post->get('setting_5');
            $cfg['show_battle_countdown']      = $this->post->get('setting_6');
            $cfg['show_castle_information']    = $this->post->get('setting_7');
            $cfg['show_current_stage']         = $this->post->get('setting_8');
            $cfg['show_next_stage']            = $this->post->get('setting_9');
            $cfg['show_battle_duration']       = $this->post->get('setting_10');
            $cfg['show_registered_guilds']     = $this->post->get('setting_11');
            $cfg['show_schedule']              = $this->post->get('setting_12');
            $cfg['schedule_date_format']       = (string) $this->post->get('setting_13', '');
            $cfg['show_widget']                = $this->post->get('setting_14');

            $stageCount = is_array($cfg['stages'] ?? null) ? count($cfg['stages']) : 0;
            foreach (['setting_stage_startday', 'setting_stage_starttime', 'setting_stage_endday', 'setting_stage_endtime'] as $arrKey) {
                $values = $this->post->get($arrKey);
                if (! $this->post->has($arrKey) || ! is_array($values) || count($values) !== $stageCount) {
                    throw new \RuntimeException('Schedule stages settings array size is not valid.');
                }
            }

            $stageStartDay  = $this->post->get('setting_stage_startday', []);
            $stageStartTime = $this->post->get('setting_stage_starttime', []);
            $stageEndDay    = $this->post->get('setting_stage_endday', []);
            $stageEndTime   = $this->post->get('setting_stage_endtime', []);

            foreach ($stageStartDay as $i => $v) {
                $cfg['stages'][$i]['start_day'] = $v;
            }
            foreach ($stageStartTime as $i => $v) {
                $cfg['stages'][$i]['start_time'] = $v;
            }
            foreach ($stageEndDay as $i => $v) {
                $cfg['stages'][$i]['end_day'] = $v;
            }
            foreach ($stageEndTime as $i => $v) {
                $cfg['stages'][$i]['end_time'] = $v;
            }

            $encoded = json_encode($cfg, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            if (file_put_contents($cfgFile, $encoded) === false) {
                throw new \RuntimeException('There has been an error while saving changes.');
            }

            MessageRenderer::toast('success', 'Settings successfully saved.');
        } catch (\Exception $ex) {
            MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    private function handleSimpleConfigSave(?string $configKey): void
    {
        if (! $this->post->has('submit_changes') || ! is_string($configKey) || $configKey === '') {
            return;
        }

        $moduleConfig = $this->moduleConfigCatalog->definition($configKey);
        if (! is_array($moduleConfig)) {
            return;
        }

        $expectedPostKeys = array_keys($moduleConfig['fields']);
        foreach ($expectedPostKeys as $postKey) {
            if (! $this->post->has($postKey)) {
                MessageRenderer::toast('error', 'Missing data (complete all fields).');
                return;
            }
        }

        if (in_array($configKey, ['addstats', 'clearskilltree'], true) && $this->post->has('setting_5') && (int) $this->post->get('setting_5') > 400) {
            MessageRenderer::toast('error', 'The required level setting can have a maximum value of 400.');
            return;
        }

        $postedValues = [];
        foreach ($expectedPostKeys as $postKey) {
            $postedValues[$postKey] = $this->post->get($postKey, '');
        }

        $saved = $this->xmlModuleConfigSaver->save($moduleConfig, $postedValues);
        if ($saved) {
            MessageRenderer::toast('success', is_string($moduleConfig['success'] ?? null) ? $moduleConfig['success'] : 'Settings successfully saved.');
            return;
        }

        MessageRenderer::toast('error', is_string($moduleConfig['error'] ?? null) ? $moduleConfig['error'] : 'There has been an error while saving changes.');
    }
}
