<?php

defined('BASEPATH') or exit('No direct script access allowed');

class InitModules
{
    /**
     * Early init modules features
     */
    public function handle()
    {
        // Ensure paths are ready early in the lifecycle
        if (!defined('APPPATH') || !defined('BASEPATH')) {
            error_log('[InitModules] APPPATH/BASEPATH not defined yet. Aborting early init.');
            return;
        }

        $appModulesPath = APPPATH . 'libraries/App_modules.php';
        $dirHelperPath  = BASEPATH . 'helpers/directory_helper.php';

        if (!file_exists($appModulesPath)) {
            error_log('[InitModules] Missing file: ' . $appModulesPath);
            return;
        }
        if (!file_exists($dirHelperPath)) {
            error_log('[InitModules] Missing file: ' . $dirHelperPath);
            return;
        }

        // Load dependencies
        include_once($appModulesPath);
        include_once($dirHelperPath);

        // Ensure loaded symbols are actually available before proceeding
        if (!class_exists('App_modules')) {
            error_log('[InitModules] Class App_modules not available after include. Skipping modules init.');
            return;
        }
        if (!function_exists('directory_map')) {
            error_log('[InitModules] directory_map() not available after including directory_helper. Skipping modules init.');
            return;
        }

        // Iterate valid modules and merge CSRF exclude URIs if present
        foreach (\App_modules::get_valid_modules() as $module) {
            $excludeUrisPath = $module['path'] . 'config' . DIRECTORY_SEPARATOR . 'csrf_exclude_uris.php';

            if (file_exists($excludeUrisPath)) {
                $uris = include_once($excludeUrisPath);

                if (is_array($uris)) {
                    hooks()->add_filter('csrf_exclude_uris', function ($current) use ($uris) {
                        return array_merge($current, $uris);
                    });
                }
            }
        }
    }
}
