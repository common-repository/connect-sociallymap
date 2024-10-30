<?php

namespace SociallymapConnect\Controllers;

use SociallymapConnect\Configs\Database\TableNameConfig;
use SociallymapConnect\Configs\System\RequiredVersionConfig;
use SociallymapConnect\Configs\PluginConfig;

class DiagnosticController extends BaseController
{
    /**
     * @return bool
     */
    private static function getAllowUrlFopen()
    {
        return ini_get('allow_url_fopen') ? true : false;
    }

    /**
     * @return mixed
     */
    public static function get_mysql_version()
    {
        global $wpdb;

        return $wpdb->get_var('SELECT VERSION() AS version');
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getPhpInfos()
    {
        $necessaryButDisabledFunctions = RequesterController::getNecessaryButDisabledFunctions();
        $allowUrlFopen = self::getAllowUrlFopen();

        $enabled = __('Actif', PluginConfig::DOMAIN_TRANSLATE);
        $disabled = __('Inactif', PluginConfig::DOMAIN_TRANSLATE);

        $phpInfos = [
            'curl_init' => [
                'present' => (!in_array('curl_init', $necessaryButDisabledFunctions, true)) ? $enabled : $disabled,
                'status' => (!in_array('curl_init', $necessaryButDisabledFunctions, true)) ? 'ok' : 'not-supported',
            ],
            'curl_exec' => [
                'present' => (!in_array('curl_exec', $necessaryButDisabledFunctions, true)) ? $enabled : $disabled,
                'status' => (!in_array('curl_exec', $necessaryButDisabledFunctions, true)) ? 'ok' : 'not-supported',
            ],
            'curl_setopt_array' => [
                'present' => (!in_array('curl_setopt_array', $necessaryButDisabledFunctions, true)) ? $enabled : $disabled,
                'status' => (!in_array('curl_setopt_array', $necessaryButDisabledFunctions, true)) ? 'ok' : 'not-supported',
            ],
            'file_get_contents' => [
                'present' => (!in_array('file_get_contents', $necessaryButDisabledFunctions, true)) ? $enabled : $disabled,
                'status' => (!in_array('file_get_contents', $necessaryButDisabledFunctions, true)) ? 'ok' : 'not-supported',
            ],
            'allow_url_fopen' => [
                'present' => $allowUrlFopen ? $enabled : $disabled,
                'status' => $allowUrlFopen ? 'ok' : 'not-supported',
            ],

        ];

        return $phpInfos;
    }

    /**
     * @return mixed
     */
    public static function getRequestSecure()
    {
        global $wpdb;

        $sql = "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name=\"" . TableNameConfig::OPTIONS . "\"";

        return $wpdb->get_var($sql);
    }

    /**
     * @return array
     */
    public static function getServerInfos()
    {
        global $wp_version;

        if (!function_exists('curl_version')) {
            $curlVersion = __('Indisponible', PluginConfig::DOMAIN_TRANSLATE);
            $curlVersionStatus = false;
        } else {
            $curlVersion = curl_version();
            $curlVersionStatus = version_compare($curlVersion['version'], RequiredVersionConfig::CURL, '>=');
        }

        $wordpressStatus = version_compare($wp_version, RequiredVersionConfig::WORDPRESS, '>=');

        $phpVersion = sprintf(
            '%d.%d.%d', PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION
        );
        $phpVersionStatus = version_compare($phpVersion, RequiredVersionConfig::PHP, '>=');

        $serverInfos = [
            'Wordpress' => ['version' => $wp_version, 'status' => $wordpressStatus],
            'PHP'       => ['version' => $phpVersion, 'status' => $phpVersionStatus],
            'CUrl'      => ['version' => $curlVersion['version'], 'status' => $curlVersionStatus],
        ];

        return $serverInfos;
    }

    public static function updateRequestSecure($secureRequest)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'options';
        $data = ['option_value' => $secureRequest];
        $where = ['option_name' => 'smc_request-secure'];

        $wpdb->update($table, $data, $where);
    }
}
