<?php
class ModuleRegistry {
    private static $modules = [];

    public static function init() {
        $moduleDirs = glob(__DIR__ . '/../modules/*', GLOB_ONLYDIR);
        foreach ($moduleDirs as $dir) {
            $metaFile = $dir . '/module.json';
            if (file_exists($metaFile)) {
                $config = json_decode(file_get_contents($metaFile), true);
                if (!empty($config['enabled'])) {
                    self::$modules[$config['id']] = $config;
                    // Load module routes
                    $routeFile = $dir . '/routes.php';
                    if (file_exists($routeFile)) {
                        require_once $routeFile;
                    }
                }
            }
        }
    }

    public static function getNavItems() {
        $nav = [];
        foreach (self::$modules as $mod) {
            if (isset($mod['nav'])) {
                $nav[] = $mod['nav'];
            }
        }
        return $nav;
    }
}