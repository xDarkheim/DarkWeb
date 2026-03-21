<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\Routing\AdmincpRouteRegistry;

final class AdmincpLayoutDataProvider
{
    private string $layoutConfigFile;
    private AdmincpRouteRegistry $routeRegistry;
    private AdmincpUrlGenerator $urlGenerator;

    public function __construct(?string $layoutConfigFile = null, ?AdmincpRouteRegistry $routeRegistry = null, ?AdmincpUrlGenerator $urlGenerator = null)
    {
        $projectRoot = dirname(__DIR__, 3);
        $this->layoutConfigFile = $layoutConfigFile ?? $projectRoot . '/config/admincp-layout.php';
        $this->routeRegistry = $routeRegistry ?? new AdmincpRouteRegistry();
        $this->urlGenerator = $urlGenerator ?? new AdmincpUrlGenerator();
    }

    /**
     * @return array<int,array{
     *     title:string,
     *     icon:string,
     *     id:string,
     *     links:array<int,array{module:string,label:string,url:string}>
     * }>
     */
    public function sidebarGroups(): array
    {
        $config = $this->load();
        $groups = [];

        foreach ($config as $group) {
            if (!is_array($group)) {
                continue;
            }

            $title = (string) ($group['title'] ?? '');
            $icon = (string) ($group['icon'] ?? '');
            $linksConfig = $group['links'] ?? [];
            if ($title === '' || $icon === '' || !is_array($linksConfig)) {
                throw new \RuntimeException('Invalid AdminCP layout group configuration.');
            }

            $links = [];
            foreach ($linksConfig as $link) {
                if (!is_array($link)) {
                    continue;
                }
                $module = (string) ($link['module'] ?? '');
                $label = (string) ($link['label'] ?? '');
                if ($module === '' || $label === '') {
                    throw new \RuntimeException('Invalid AdminCP layout link configuration.');
                }
                if (!is_array($this->routeRegistry->routeFor($module))) {
                    throw new \RuntimeException("AdminCP layout references unknown module '{$module}'.");
                }
                $links[] = [
                    'module' => $module,
                    'label' => $label,
                    'url' => $this->urlGenerator->base($module),
                ];
            }

            $groups[] = [
                'title' => $title,
                'icon' => $icon,
                'id' => 'sm_' . preg_replace('/\W/', '', $title),
                'links' => $links,
            ];
        }

        return $groups;
    }

    /** @return array<int,mixed> */
    private function load(): array
    {
        if (!is_file($this->layoutConfigFile)) {
            throw new \RuntimeException('AdminCP layout config file not found.');
        }

        /** @var mixed $data */
        $data = include $this->layoutConfigFile;
        if (!is_array($data)) {
            throw new \RuntimeException('AdminCP layout config must return an array.');
        }

        return $data;
    }
}

