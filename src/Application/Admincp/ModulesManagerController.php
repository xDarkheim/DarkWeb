<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

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

        $configKey = null;
        $configFilePath = null;
        if (isset($_GET['config'])) {
            $usercpModules = ['addstats', 'buyzen', 'clearpk', 'clearskilltree', 'myaccount', 'myemail', 'mypassword', 'reset', 'resetstats', 'unstick', 'vote'];
            $configKey = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $_GET['config']));
            $subDir = in_array($configKey, $usercpModules, true) ? 'usercp/' : '';
            $filePath = __PATH_VIEWS__ . 'admincp/mconfig/' . $subDir . $configKey . '.php';
            if (is_file($filePath)) {
                $configFilePath = $filePath;
            } else {
                message('error', 'Invalid module.');
            }
        }

        $this->view->render('admincp/modulesmanager', [
            'globalModules' => $cmsModules['_global'],
            'usercpModules' => $cmsModules['_usercp'],
            'selectedConfigKey' => $configKey,
            'selectedConfigFilePath' => $configFilePath,
        ]);
    }
}

