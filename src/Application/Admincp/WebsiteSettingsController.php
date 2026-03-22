<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Config\ConfigRepository;
use Darkheim\Infrastructure\View\ViewRenderer;

final class WebsiteSettingsController
{
    private ViewRenderer $view;

    /** @var array<int,string> */
    private array $allowedSettings = [
        'settings_submit',
        'system_active',
        'error_reporting',
        'website_theme',
        'maintenance_page',
        'server_name',
        'website_title',
        'website_meta_description',
        'website_meta_keywords',
        'website_forum_link',
        'language_switch_active',
        'language_default',
        'language_debug',
        'plugins_system_enable',
        'ip_block_system_enable',
        'player_profiles',
        'guild_profiles',
        'username_min_len',
        'username_max_len',
        'password_min_len',
        'password_max_len',
        'social_link_facebook',
        'social_link_instagram',
        'social_link_discord',
        'server_info_season',
        'server_info_exp',
        'server_info_masterexp',
        'server_info_drop',
        'maximum_online',
    ];

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (isset($_POST['settings_submit'])) {
            try {
                $setting = $this->validatedSettingsFromPost();

                $cmsConfigurations = BootstrapContext::configProvider()?->cms() ?? [];
                foreach (array_keys($setting) as $settingName) {
                    if (! in_array($settingName, $this->allowedSettings, true)) {
                        throw new \RuntimeException('One or more submitted setting is not editable.');
                    }
                    $cmsConfigurations[$settingName] = $setting[$settingName];
                }

                new ConfigRepository(__PATH_CONFIGS__)->saveCms($cmsConfigurations);

                \Darkheim\Application\View\MessageRenderer::toast('success', 'Settings successfully saved!');
            } catch (\Exception $ex) {
                \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
            }
        }

        $rows = $this->rowsSchema();
        foreach ($rows as &$row) {
            $rawValue = \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue((string) $row['key'], true);
            if ($row['type'] === 'bool') {
                $row['value'] = in_array($rawValue, [true, 1, '1', 'true'], true) ? '1' : '0';
            } else {
                $row['value'] = (string) $rawValue;
            }
        }
        unset($row);

        $this->view->render('admincp/websitesettings', [
            'rows' => $rows,
        ]);
    }

    /** @return array<string,mixed> */
    private function validatedSettingsFromPost(): array
    {
        $setting = [];

        $setting['system_active']   = $this->expectBool('system_active', 'Invalid Website Status setting.');
        $setting['error_reporting'] = $this->expectBool('error_reporting', 'Invalid Error Reporting setting.');

        if (! isset($_POST['website_theme'])) {
            throw new \RuntimeException('Invalid Default Template setting.');
        }
        if (! file_exists(__PATH_THEMES__ . $_POST['website_theme'] . '/index.php')) {
            throw new \RuntimeException('The selected theme doesn\'t exist.');
        }
        $setting['website_theme'] = (string) $_POST['website_theme'];

        if (! isset($_POST['maintenance_page']) || ! Validator::Url($_POST['maintenance_page'])) {
            throw new \RuntimeException('The maintenance page setting is not a valid URL.');
        }
        $setting['maintenance_page'] = (string) $_POST['maintenance_page'];

        foreach (['server_name', 'website_title', 'website_meta_description', 'website_meta_keywords'] as $name) {
            if (! isset($_POST[$name])) {
                throw new \RuntimeException('Invalid setting (' . $name . ')');
            }
            $setting[$name] = (string) $_POST[$name];
        }

        if (! isset($_POST['website_forum_link']) || ! Validator::Url($_POST['website_forum_link'])) {
            throw new \RuntimeException('The forum link setting is not a valid URL.');
        }
        $setting['website_forum_link'] = (string) $_POST['website_forum_link'];

        $setting['language_switch_active'] = $this->expectBool('language_switch_active', 'Invalid Language Switch setting.');

        if (! isset($_POST['language_default'])) {
            throw new \RuntimeException('Invalid Default Language setting.');
        }
        if (! file_exists(__PATH_LANGUAGES__ . $_POST['language_default'] . '/language.php')) {
            throw new \RuntimeException('The default language doesn\'t exist.');
        }
        $setting['language_default'] = (string) $_POST['language_default'];

        $setting['language_debug']         = $this->expectBool('language_debug', 'Invalid Language Debug setting.');
        $setting['plugins_system_enable']  = $this->expectBool('plugins_system_enable', 'Invalid Plugin System setting.');
        $setting['ip_block_system_enable'] = $this->expectBool('ip_block_system_enable', 'Invalid IP Block System setting.');
        $setting['player_profiles']        = $this->expectBool('player_profiles', 'Invalid setting (player_profiles)');
        $setting['guild_profiles']         = $this->expectBool('guild_profiles', 'Invalid setting (guild_profiles)');

        foreach (['username_min_len', 'username_max_len', 'password_min_len', 'password_max_len'] as $numSetting) {
            if (! isset($_POST[$numSetting]) || ! Validator::UnsignedNumber($_POST[$numSetting])) {
                throw new \RuntimeException('Invalid setting (' . $numSetting . ')');
            }
            $setting[$numSetting] = (string) $_POST[$numSetting];
        }

        foreach ([
            'social_link_facebook'  => 'The facebook link setting is not a valid URL.',
            'social_link_instagram' => 'The instagram link setting is not a valid URL.',
            'social_link_discord'   => 'The discord link setting is not a valid URL.',
        ] as $key => $errorMessage) {
            if (isset($_POST[$key]) && Validator::hasValue($_POST[$key]) && ! Validator::Url($_POST[$key])) {
                throw new \RuntimeException($errorMessage);
            }
            $setting[$key] = (string) ($_POST[$key] ?? '');
        }

        foreach (['server_info_season', 'server_info_exp', 'server_info_masterexp', 'server_info_drop'] as $name) {
            $setting[$name] = (string) ($_POST[$name] ?? '');
        }

        if (isset($_POST['maximum_online']) && Validator::hasValue($_POST['maximum_online']) && ! Validator::UnsignedNumber($_POST['maximum_online'])) {
            throw new \RuntimeException('Invalid setting (maximum_online)');
        }
        $setting['maximum_online'] = (string) ($_POST['maximum_online'] ?? '');

        return $setting;
    }

    private function expectBool(string $key, string $errorMessage): bool
    {
        if (! isset($_POST[$key]) || ! in_array($_POST[$key], ['0', '1', 0, 1], true)) {
            throw new \RuntimeException($errorMessage);
        }

        return $_POST[$key] == 1;
    }

    /**
     * @return array<int,array{key:string,label:string,description:string,type:string,required?:bool,value?:string}>
     */
    private function rowsSchema(): array
    {
        return [
            ['key' => 'system_active', 'label' => 'Website Status', 'description' => 'Enables/disables your website. If disabled, visitors are redirected to maintenance page.', 'type' => 'bool'],
            ['key' => 'error_reporting', 'label' => 'Debug Mode', 'description' => 'Enable only when you need to display PHP/runtime errors.', 'type' => 'bool'],
            ['key' => 'website_theme', 'label' => 'Default Template', 'description' => 'Your website default theme folder name.', 'type' => 'text', 'required' => true],
            ['key' => 'maintenance_page', 'label' => 'Maintenance Page URL', 'description' => 'Full URL used while website status is disabled.', 'type' => 'text', 'required' => true],
            ['key' => 'server_name', 'label' => 'Server Name', 'description' => 'Your MU Online server name.', 'type' => 'text', 'required' => true],
            ['key' => 'website_title', 'label' => 'Website Title', 'description' => 'Main website title.', 'type' => 'text', 'required' => true],
            ['key' => 'website_meta_description', 'label' => 'Meta Description', 'description' => 'Server description for search engines.', 'type' => 'text', 'required' => true],
            ['key' => 'website_meta_keywords', 'label' => 'Meta Keywords', 'description' => 'Comma-separated keywords for search engines.', 'type' => 'text', 'required' => true],
            ['key' => 'website_forum_link', 'label' => 'Forum Link', 'description' => 'Full URL to your forum.', 'type' => 'text', 'required' => true],
            ['key' => 'language_switch_active', 'label' => 'Language Switching', 'description' => 'Enable/disable language switch UI.', 'type' => 'bool'],
            ['key' => 'language_default', 'label' => 'Default Language', 'description' => 'Default language folder key.', 'type' => 'text', 'required' => true],
            ['key' => 'language_debug', 'label' => 'Language Debug', 'description' => 'Shows phrase keys in hover-tip. Keep disabled in production.', 'type' => 'bool'],
            ['key' => 'plugins_system_enable', 'label' => 'Plugin System Status', 'description' => 'Enable/disable plugin system.', 'type' => 'bool'],
            ['key' => 'ip_block_system_enable', 'label' => 'IP Block System Status', 'description' => 'Enable/disable web IP block system.', 'type' => 'bool'],
            ['key' => 'player_profiles', 'label' => 'Player Profile Links', 'description' => 'If enabled, player names link to profile page.', 'type' => 'bool'],
            ['key' => 'guild_profiles', 'label' => 'Guild Profile Links', 'description' => 'If enabled, guild names link to profile page.', 'type' => 'bool'],
            ['key' => 'username_min_len', 'label' => 'Username Minimum Length', 'description' => 'Minimum allowed username length.', 'type' => 'text', 'required' => true],
            ['key' => 'username_max_len', 'label' => 'Username Maximum Length', 'description' => 'Maximum allowed username length.', 'type' => 'text', 'required' => true],
            ['key' => 'password_min_len', 'label' => 'Password Minimum Length', 'description' => 'Minimum allowed password length.', 'type' => 'text', 'required' => true],
            ['key' => 'password_max_len', 'label' => 'Password Maximum Length', 'description' => 'Maximum allowed password length.', 'type' => 'text', 'required' => true],
            ['key' => 'social_link_facebook', 'label' => 'Facebook Link', 'description' => 'Link to your Facebook page.', 'type' => 'text', 'required' => false],
            ['key' => 'social_link_instagram', 'label' => 'Instagram Link', 'description' => 'Link to your Instagram page.', 'type' => 'text', 'required' => false],
            ['key' => 'social_link_discord', 'label' => 'Discord Link', 'description' => 'Link to your Discord invite.', 'type' => 'text', 'required' => false],
            ['key' => 'server_info_season', 'label' => 'Server Info: Season', 'description' => 'Leave empty to hide this info block.', 'type' => 'text', 'required' => false],
            ['key' => 'server_info_exp', 'label' => 'Server Info: Experience', 'description' => 'Leave empty to hide this info block.', 'type' => 'text', 'required' => false],
            ['key' => 'server_info_masterexp', 'label' => 'Server Info: Master Experience', 'description' => 'Leave empty to hide this info block.', 'type' => 'text', 'required' => false],
            ['key' => 'server_info_drop', 'label' => 'Server Info: Drop', 'description' => 'Leave empty to hide this info block.', 'type' => 'text', 'required' => false],
            ['key' => 'maximum_online', 'label' => 'Maximum Online Players', 'description' => 'Maximum allowed online players. Leave empty to hide.', 'type' => 'text', 'required' => false],
        ];
    }
}
