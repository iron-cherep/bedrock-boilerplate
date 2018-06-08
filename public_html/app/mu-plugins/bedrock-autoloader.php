<?php
/**
 * Plugin Name: Bedrock Autoloader
 * Plugin URI: https://github.com/roots/bedrock/
 * Description: Автозагрузчик, позволяющий использовать стандартные плагины как must-use плагины. Автозагруженные плагины подключаются в процессе загрузки mu-плагина. Звёздочка (*) после имени плагина обозначает, что плагину необходима автозагрузка.
 * Version: 1.0.0
 * Author: Roots
 * Author URI: https://roots.io/
 * License: MIT License
 */

namespace Roots\Bedrock;

if (!is_blog_installed()) {
    return;
}

/**
 * Class Autoloader
 * @package Roots\Bedrock
 * @author Roots
 * @link https://roots.io/
 */
class Autoloader
{
    /** @var array Хранит кэш автозагрузчика и конфигурацию сайта */
    private static $cache;

    /** @var array Автозагружаемые плагины */
    private static $auto_plugins;

    /** @var array Автозагружаемые mu-плагины */
    private static $mu_plugins;

    /** @var int Число плагинов */
    private static $count;

    /** @var array Активированные плагины */
    private static $activated;

    /** @var string Относительный путь к директории mu-plugins */
    private static $relative_path;

    /** @var static Инстанс синглтона */
    private static $_single;

    /**
     * Создать синглтон, назначить переменные и добавить хуки WordPress
     */
    public function __construct()
    {
        if (isset(self::$_single)) {
            return;
        }

        self::$_single = $this;
        self::$relative_path = '/../' . basename(__DIR__);

        if (is_admin()) {
            add_filter('show_advanced_plugins', [$this, 'showInAdmin'], 0, 2);
        }

        $this->loadPlugins();
    }

   /**
    * Запустить проверки и начать автозагрузку наших плагинов.
    */
    public function loadPlugins()
    {
        $this->checkCache();
        $this->validatePlugins();
        $this->countPlugins();

        array_map(static function () {
            include_once(WPMU_PLUGIN_DIR . '/' . func_get_args()[0]);
        }, array_keys(self::$cache['plugins']));

        $this->pluginHooks();
    }

    /**
     * Фильтр show_advanced_plugins отображает список автозагруженных плагинов.
     *
     * @param $show bool Отображать автозагружаемые плагины указанного типа
     * @param $type string Тип плагина, например `mustuse` или `dropins`
     * @return bool Возвращаем `false`, чтобы не дать WordPress переопределять наши данные
     * {@internal Мы добавили информацию о плагине, так что возвращаем false, чтобы деактивировать фильтр}
     */
    public function showInAdmin($show, $type)
    {
        $screen = get_current_screen();
        $current = is_multisite() ? 'plugins-network' : 'plugins';

        if ($screen->{'base'} != $current || $type != 'mustuse' || !current_user_can('activate_plugins')) {
            return $show;
        }

        $this->updateCache();

        self::$auto_plugins = array_map(function ($auto_plugin) {
            $auto_plugin['Name'] .= ' *';
            return $auto_plugin;
        }, self::$auto_plugins);

        $GLOBALS['plugins']['mustuse'] = array_unique(array_merge(self::$auto_plugins, self::$mu_plugins), SORT_REGULAR);

        return false;
    }

    /**
     * Загружает кэш или вызывает его обновление.
     */
    private function checkCache()
    {
        $cache = get_site_option('bedrock_autoloader');

        if ($cache === false) {
            $this->updateCache();
            return;
        }

        self::$cache = $cache;
    }

    /**
     * Берёт плагины и mu-плагины из директории mu-плагинов и удаляет повторяющиеся экземпляры.
     * Сравнивает версию закэшированных плагинов с версией заново активированных.
     * После этого обновляет кэш.
     */
    private function updateCache()
    {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        self::$auto_plugins = get_plugins(self::$relative_path);
        self::$mu_plugins   = get_mu_plugins();
        $plugins            = array_diff_key(self::$auto_plugins, self::$mu_plugins);
        $rebuild            = !is_array(self::$cache['plugins']);
        self::$activated    = ($rebuild) ? $plugins : array_diff_key($plugins, self::$cache['plugins']);
        self::$cache        = array('plugins' => $plugins, 'count' => $this->countPlugins());

        update_site_option('bedrock_autoloader', self::$cache);
    }

    /**
     * Вызывает хуки, которые были бы вызваны при нормальной загрузке плагинов.
     * При удалении автозагруженных плагинов их файлы так же удаляются, так что
     * их не нужно деактивировать или деинсталировать.
     */
    private function pluginHooks()
    {
        if (!is_array(self::$activated)) {
            return;
        }

        foreach (self::$activated as $plugin_file => $plugin_info) {
            do_action('activate_' . $plugin_file);
        }
    }

    /**
     * Проверяет, что файлы плагина существуют, и обновляет кэш, если это не так.
     */
    private function validatePlugins()
    {
        foreach (self::$cache['plugins'] as $plugin_file => $plugin_info) {
            if (!file_exists(WPMU_PLUGIN_DIR . '/' . $plugin_file)) {
                $this->updateCache();
                break;
            }
        }
    }

    /**
     * Подсчитывает число автозагруженных плагинов.
     *
     * Подсчитывает наши плагины (только один раз) пересчётом директорий в mu-plugins.
     * Если число изменилось - обновляет кэш.
     *
     * @return int Число автозагруженных плагинов
     */
    private function countPlugins()
    {
        if (isset(self::$count)) {
            return self::$count;
        }

        $count = count(glob(WPMU_PLUGIN_DIR . '/*/', GLOB_ONLYDIR | GLOB_NOSORT));

        if (!isset(self::$cache['count']) || $count != self::$cache['count']) {
            self::$count = $count;
            $this->updateCache();
        }

        return self::$count;
    }
}

new Autoloader();
