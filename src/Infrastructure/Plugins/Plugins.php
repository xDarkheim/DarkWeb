<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Plugins;

use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Cache\CacheBuilder;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Runtime\Contracts\SessionStore;
use Darkheim\Infrastructure\Runtime\Native\NativeSessionStore;

/**
 * Plugin installation, activation, cache rebuild.
 */
class Plugins
{
    protected $db;
    private ?SessionStore $session = null;

    public function __construct(?SessionStore $session = null)
    {
        $this->db      = Connection::Database('MuOnline');
        $this->session = $session ?? new NativeSessionStore();
    }

    public function importPlugin($_FILE): void
    {
        if ($_FILE['file']['type'] == 'text/xml') {
            $xml        = simplexml_load_string(file_get_contents($_FILE['file']['tmp_name']));
            $pluginDATA = self::xmlToArray($xml->children());
            if ($this->checkXML($pluginDATA)) {
                if ($this->checkCompatibility($pluginDATA['compatibility'])) {
                    if ($this->checkPluginDirectory($pluginDATA['folder'])) {
                        if ($this->checkFiles($pluginDATA['files'], $pluginDATA['folder'])) {
                            $install = $this->installPlugin($pluginDATA);
                            if ($install) {
                                MessageRenderer::toast('success', 'Plugin successfully imported!');
                            } else {
                                MessageRenderer::toast('error', 'Could not import plugin.');
                            }
                            if (! $this->rebuildPluginsCache()) {
                                MessageRenderer::toast('error', 'Could not update plugins cache data, make sure the file exists and it\'s writable!');
                            }
                        } else {
                            MessageRenderer::toast('error', 'Plugin file(s) missing.');
                        }
                    } else {
                        MessageRenderer::toast('error', 'Plugin folder not found, please make sure you upload it to the correct path.');
                    }
                } else {
                    MessageRenderer::toast('error', 'The plugin is not compatible with your current version.');
                }
            } else {
                MessageRenderer::toast('error', 'Invalid file or missing data.');
            }
        } else {
            MessageRenderer::toast('error', 'Invalid file type (only XML).');
        }
    }

    private function checkXML($array): bool
    {
        return array_key_exists('name', $array)
            && array_key_exists('author', $array)
            && array_key_exists('version', $array)
            && array_key_exists('compatibility', $array)
            && array_key_exists('folder', $array)
            && array_key_exists('files', $array)
            && Validator::hasValue($array['name'])
            && Validator::hasValue($array['author'])
            && Validator::hasValue($array['version'])
            && Validator::hasValue($array['folder'])
            && is_array($array['compatibility'])
            && is_array($array['files']);
    }

    private function checkCompatibility($array): bool
    {
        if (! array_key_exists('darkheim', $array)) {
            return false;
        }
        if (is_array($array['darkheim'])) {
            return in_array(__CMS_VERSION__, $array['darkheim'], true);
        }
        return __CMS_VERSION__ == $array['darkheim'];
    }

    private function checkPluginDirectory($name): bool
    {
        return file_exists($this->pluginPath($name)) && is_dir($this->pluginPath($name));
    }

    private function checkFiles($array, $plugin_name): bool
    {
        if (! array_key_exists('file', $array)) {
            return false;
        }
        if (is_array($array['file'])) {
            return array_all(
                $array['file'],
                fn($thisFile) => file_exists(
                    $this->pluginPath($plugin_name) . $thisFile,
                ),
            );
        }
        return file_exists($this->pluginPath($plugin_name) . $array['file']);
    }

    private function pluginPath($name): string
    {
        return __PATH_PLUGINS__ . $name . '/';
    }

    /** @return array<string, mixed>
     * @throws \JsonException
     */
    private static function xmlToArray(
        \SimpleXMLElement $object,
    ): array {
        return json_decode(json_encode($object, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    private function installPlugin($pluginDATA): bool
    {
        $compatibility = $pluginDATA['compatibility']['darkheim'];
        $files         = $pluginDATA['files']['file'];
        if (is_array($pluginDATA['compatibility']['darkheim'])) {
            $compatibility = implode('', $pluginDATA['compatibility']['darkheim']);
        }
        if (is_array($pluginDATA['files']['file'])) {
            $files = implode('', $pluginDATA['files']['file']);
        }
        $data = [
            $pluginDATA['name'], $pluginDATA['author'], $pluginDATA['version'],
            $compatibility, $pluginDATA['folder'], $files, 1, time(), (string) $this->session()->get('username', ''),
        ];

        return $this->db->query('INSERT INTO ' . Plugins . ' (name, author, version, compatibility, folder, files, status, install_date, installed_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', $data);
    }

    public function retrieveInstalledPlugins(): false|array|null
    {
        return $this->db->query_fetch('SELECT * FROM ' . Plugins . ' ORDER BY id');
    }

    public function updatePluginStatus($plugin_id, $new_status): void
    {
        $this->db->query('UPDATE ' . Plugins . ' SET status = ? WHERE id = ?', [$new_status, $plugin_id]);
        if (! $this->rebuildPluginsCache()) {
            MessageRenderer::toast('error', 'Could not update plugins cache data, make sure the file exists and it\'s writable!');
        }
    }

    public function uninstallPlugin($plugin_id): bool
    {
        return $this->db->query('DELETE FROM ' . Plugins . ' WHERE id = ?', [$plugin_id]);
    }

    public function rebuildPluginsCache(): bool
    {
        $cache   = new CacheRepository(__PATH_CACHE__);
        $plugins = $this->db->query_fetch('SELECT * FROM ' . Plugins . ' WHERE status = 1 ORDER BY id');
        if (! is_array($plugins)) {
            return $cache->save('plugins.cache', '');
        }
        foreach ($plugins as $key => $row) {
            $compatibility = explode(',', $row['compatibility']);
            if (! in_array(__CMS_VERSION__, $compatibility)) {
                continue;
            }
            $files                          = explode(',', $row['files']);
            $plugins[$key]['compatibility'] = $compatibility;
            $plugins[$key]['files']         = $files;
        }
        return $cache->save('plugins.cache', CacheBuilder::encode($plugins));
    }

    private function session(): SessionStore
    {
        if (! $this->session instanceof SessionStore) {
            $this->session = new NativeSessionStore();
        }

        return $this->session;
    }
}
