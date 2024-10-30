<?php

require_once __DIR__ . '/Includes/Autoloader.php';

$loader = new Autoloader();
$loader->register();
$loader->addNamespace('SociallymapConnect', __DIR__);

use SociallymapConnect\Configs\Database\OldTableNameConfig;
use SociallymapConnect\Configs\Database\TableNameConfig;
use SociallymapConnect\Configs\LogConfig;
use SociallymapConnect\Configs\PluginConfig;
use SociallymapConnect\Configs\SupportedPlugin\YoastConfig;
use SociallymapConnect\Configs\System\RequiredVersionConfig;
use SociallymapConnect\Controllers\BaseController;
use SociallymapConnect\Controllers\RequesterController;
use SociallymapConnect\Enums\Publication\Image;
use SociallymapConnect\Enums\Publication\Type;
use SociallymapConnect\Includes\Config;
use SociallymapConnect\Includes\Logger;
use SociallymapConnect\Includes\Templater;
use SociallymapConnect\Models\Entity;
use SociallymapConnect\Models\EntityRepository;
use SociallymapConnect\Models\MessageRepository;
use SociallymapConnect\Services\ExceptionHandler;

class SociallymapConnectPlugin
{
    private $controller;
    private $templater;
    private $wpdb;
    protected $config;

    public function __construct($config)
    {
        global $wpdb;

        $this->config = new Config($config);
        $logDriver = $this->config->getLogDriver();
        Logger::setLogDriver($logDriver);

        $this->wpdb = $wpdb;

        $this->checkRequirements();

        $this->templater = new Templater();

        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        add_action('init', [$this, 'initPlugin']);
        add_action('template_redirect', [$this, 'dealsWithSociallymap']);
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('init', [$this, 'logsExport']);
        add_action('wp_head', [$this, 'addCanonical'], 0, 1);

        remove_action('wp_head', 'rel_canonical');

    }

    private function checkIfDebugMode()
    {
        return defined('WP_DEBUG') && true === WP_DEBUG;
    }

    public static function initLogger()
    {
        Logger::initTable();


        $cleanLogSince = new DateTime();
        $cleanLogSince->modify(LogConfig::CLEAR_LOG_SINCE);
        Logger::cleanLogsInDatase($cleanLogSince);
    }

    public static function initRewrite()
    {
        add_rewrite_tag('%sociallymap-plugin%', '1');
        add_rewrite_rule('sociallymap', 'index.php?sociallymap-plugin=1', 'top');
    }

    public static function loadAssets()
    {
        wp_enqueue_style('readmore', plugin_dir_url(__FILE__) . 'assets/css/sociallymap-connect.css');
        wp_enqueue_style('modalw-style', plugin_dir_url(__FILE__) . 'assets/modalw/modalw-style.css');

        wp_enqueue_script('jquery');
        wp_enqueue_script('modalw-script', plugin_dir_url(__FILE__) . 'assets/modalw/modal-windows.js');
    }

    public function addAdminErrorNotice($message)
    {
        $noticeTpl = '<div class="notice notice-error is-dismissible"><p>%s</p></div>';
        $noticeHtml = sprintf($noticeTpl, $message);

        echo $noticeHtml;
    }

    public function addAdminMenu()
    {
        add_menu_page(
            'Sociallymap',
            'Sociallymap',
            'manage',
            'sociallymap',
            function () {
                echo BaseController::forward('PluginController::home');
            },
            plugin_dir_url(__FILE__) . 'assets/images/icon.png'
        );

        add_submenu_page(
            'sociallymap',
            'Mes entités',
            __('Mes entités', PluginConfig::DOMAIN_TRANSLATE),
            'manage_options',
            'sociallymap-entity-list',
            function () {
                echo BaseController::forward('PluginController::home');
            }
        );

        add_submenu_page(
            'sociallymap',
            'Ajouter une entité',
            __('Ajouter une entité', PluginConfig::DOMAIN_TRANSLATE),
            'manage_options',
            'sociallymap-entity-add',
            function () {
                echo BaseController::forward('PluginController::entityAdd');
            }
        );

        add_submenu_page(
            null,
            'edit entity',
            'Editer lien',
            'manage_options',
            'sociallymap-entity-edit',
            function () {
                echo BaseController::forward('PluginController::entityEdit');
            }
        );

        add_submenu_page(
            null,
            'delete entity',
            'Supprimer lien',
            'manage_options',
            'sociallymap-entity-delete',
            function () {
                echo BaseController::forward('PluginController::entityDelete');
            }
        );

        add_submenu_page(
            'sociallymap',
            'Documentation',
            'Documentation',
            'manage_options',
            'sociallymap-documentation',
            function () {
                echo BaseController::forward('PluginController::documentation');
            }
        );

        add_submenu_page(
            'sociallymap',
            'server infos',
            __('Diagnostique', PluginConfig::DOMAIN_TRANSLATE),
            'manage_options',
            'sociallymap-diagnostic',
            function () {
                echo BaseController::forward('PluginController::diagnostic');
            }
        );

        if ($this->checkIfDebugMode()) {
            add_submenu_page(
                'sociallymap',
                'logger',
                'Logs',
                'manage_options',
                'sociallymap-logsManager',
                function () {
                    echo BaseController::forward('LogController::logsManager');
                }
            );
        }

    }

    public function addCanonical($url = null)
    {
        if (null !== $url) {
            echo '<link rel="canonical" href="' . $url . '"/>' . "\n";
        } else {

            if (!SociallymapConnectPlugin::checkPluginsState(YoastConfig::FILE)) {
                global $wp;

                $current_url = home_url(add_query_arg([], $wp->request));
                $meta = get_post_meta(get_the_ID(), TableNameConfig::SMC_POST_META, true);

                $meta = ($meta === "") ? $current_url : $meta;

                echo '<link rel="canonical" href="' . $meta . '"/>' . "\n";
            }
        }
    }

    public function addErrorNoticeCurlVersion()
    {
        $curlVersions = curl_version();
        $currentCurlVersion = $curlVersions['version'];

        $noticeMessageTpl =
            __(
                'Le plugin Sociallymap Connect nécessite au minimum Curl %s. La version actuellement installée est %s. Veuillez contacter votre hébergeur pour remédier au problème.',
                PluginConfig::DOMAIN_TRANSLATE
            );
        $noticeMessage = sprintf($noticeMessageTpl, RequiredVersionConfig::CURL, $currentCurlVersion);

        $this->addAdminErrorNotice($noticeMessage);
    }

    public function addErrorNoticeDisabledFunctions()
    {
        $disabledFunctions = RequesterController::getNecessaryButDisabledFunctions();

        $noticeMessageTpl =
            __(
                'Le plugin Sociallymap Connect nécessite les functions suivantes: %s. Elles sont actuellement désactivées par votre configuration. Veuillez contacter votre hébergeur pour remédier au problème.',
                PluginConfig::DOMAIN_TRANSLATE
            );
        $noticeMessage = sprintf($noticeMessageTpl, implode(', ', $disabledFunctions));

        $this->addAdminErrorNotice($noticeMessage);
    }

    public function addErrorNoticePhpVersion()
    {
        $noticeMessageTpl =
            __(
                'Le plugin Sociallymap Connect nécessite au minimum PHP %s. Veuillez contacter votre hébergeur pour remédier au problème.',
                PluginConfig::DOMAIN_TRANSLATE
            );
        $noticeMessage = sprintf($noticeMessageTpl, RequiredVersionConfig::PHP);

        $this->addAdminErrorNotice($noticeMessage);
    }

    public function addErrorNoticeWordpressVersion()
    {
        $noticeMessageTpl =
            __(
                'Le plugin Sociallymap Connect nécessite au minimum Wordpress %s. Veuillez contacter votre hébergeur pour remédier au problème.',
                PluginConfig::DOMAIN_TRANSLATE
            );
        $noticeMessage = sprintf($noticeMessageTpl, RequiredVersionConfig::WORDPRESS);

        $this->addAdminErrorNotice($noticeMessage);
    }

    public function addErrorNoticeYoastSEO()
    {
        $entityRepository = new EntityRepository();
        $entitties = $entityRepository->findAll(['compatibility_yoastseo' => true]);

        if (!empty($entitties)) {
            foreach ($entitties as $entity) {
                $listEntities[] = $entity->getName();
            }
            $data = implode(', ', $listEntities);

            $noticeMessageTpl =
                __(
                    'La compatibilité avec Yoast SEO est activée. Cependant il semblerait que ce plugin ne soit pas présent ou actif. %s Liste des entités concernaient : %s',
                    PluginConfig::DOMAIN_TRANSLATE
                );
            $noticeMessage = sprintf($noticeMessageTpl, '<br>', $data);

            $this->addAdminErrorNotice($noticeMessage);
        }
    }

    public function checkCurlVersion()
    {
        $curlVersions = function_exists('curl_version') ? curl_version() : null;

        if (null === $curlVersions) {
            add_action('admin_notices', [$this, 'addErrorNoticeCurlUnavailable']);
        } elseif (version_compare($curlVersions['version'], RequiredVersionConfig::CURL, '<')) {
            add_action('admin_notices', [$this, 'addErrorNoticeCurlVersion']);
        }
    }

    public function checkDisabledFunctions()
    {
        if (RequesterController::addNoticeDisabledFunctions()) {
            add_action('admin_notices', [$this, 'addErrorNoticeDisabledFunctions']);
        }
    }

    public function checkPhpVersion()
    {
        if (version_compare(phpversion(), RequiredVersionConfig::PHP, '<')) {
            add_action('admin_notices', [$this, 'addErrorNoticePhpVersion']);
        }
    }

    public static function checkPluginsState($name)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $allPlugins = get_plugins();
        $pluginInstalled = array_key_exists($name, $allPlugins);

        $pluginEnabled = is_plugin_active($name);

        return ($pluginInstalled && $pluginEnabled);
    }

    public function checkRequirements()
    {
        $this->checkPhpVersion();
        $this->checkCurlVersion();
        $this->checkWordpressVersion();
        $this->checkDisabledFunctions();
        $this->checkYoastSEO();
    }

    public function checkWordpressVersion()
    {
        if (version_compare(PHP_VERSION, RequiredVersionConfig::WORDPRESS, '<')) {
            add_action('admin_notices', [$this, 'addErrorNoticeWordpressVersion']);
        }
    }

    public function checkYoastSEO()
    {
        if (!self::checkPluginsState(YoastConfig::FILE)) {
            add_action('admin_notices', [$this, 'addErrorNoticeYoastSEO']);
        }
    }

    public function dealsWithSociallymap()
    {
        global $wp_query;

        if ($wp_query->get('sociallymap-plugin')) {
            try {
                if (!isset($_POST['entityId'], $_POST['token'])) {
                    $errorMessage =
                        'Error 400. Bad Request. Missing parameters "entityId" and "token" in data request.';
                    Logger::logError($errorMessage);
                    BaseController::forwardError(400, $errorMessage);
                } else {
                    $poolData = [];
                    foreach ($_POST as $key => $value) {
                        $poolData[$key] = sanitize_text_field($value);
                    }
                }

                if ($poolData['token'] === 'connection-test') {
                    Logger::logInfo('Ping Request received from Sociallymap');
                    BaseController::forward('PluginController::connectionTest', [$poolData['entityId']]);
                } else {
                    $environment = 'prod';

                    if (isset($_POST['environment'])) {
                        $environment = $_POST['environment'];
                    }
                    Logger::logInfo(
                        sprintf(
                            'Messages available Request received from Sociallymap for environment %s. Entity ID -> %s',
                            $environment,
                            $poolData['entityId']
                        )
                    );
                    BaseController::forward(
                        'PluginController::getSociallymapMessages',
                        [$poolData['entityId'], $poolData['token'], $environment]
                    );
                }
            } catch (Exception $exception) {
                ExceptionHandler::handleException($exception);
            }
        }
    }

    public static function destroyConfig()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'options';

        $wpdb->delete($tableName, ['option_name' => 'smc_request-secure']);
    }

    public static function getOptionValue($optionId, $entityId)
    {
        global $wpdb;
        $sql = sprintf(
            'SELECT * FROM %s WHERE entity_id=%d AND options_id=%d',
            $wpdb->prefix . 'sm_entity_options',
            $entityId,
            $optionId
        );

        $result = $wpdb->get_results($sql, ARRAY_A);
        if ($optionId === 1) {
            return $result;
        }

        return $result[0]['value'];
    }

    public static function initConfig()
    {
        global $wpdb;

        if (!$wpdb->get_var(
            "SELECT option_name FROM " .
            $wpdb->prefix .
            "options WHERE option_name ='" .
            TableNameConfig::OPTIONS .
            "';"
        )) {
            $wpdb->insert(
                $wpdb->prefix . "options",
                [
                    'option_name' => TableNameConfig::OPTIONS,
                    'option_value' => '1',
                    'autoload' => 'no',
                ]
            );

            Logger::logInfo(sprintf('Config table %s for value %s', $wpdb->prefix, TableNameConfig::OPTIONS));
        }
    }

    public static function initDatabase()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $entityRepository = new EntityRepository();
        $entityRepository->initTable();

        $messageRepository = new MessageRepository();
        $messageRepository->initTable();

        Logger::initTable();

        self::initConfig();
    }

    public static function initPlugin()
    {
        register_uninstall_hook(__FILE__, ['SociallymapConnectPlugin', 'uninstallPlugin']);

        self::upgradeSociallymapConnectPlugin();
        self::initDatabase();
        self::initLogger();
        self::initRewrite();
        self::loadAssets();
    }

    public function loadTemplate($params)
    {
        $this->controller->$params();
    }

    public function loadTextDomain()
    {
        $dir = __DIR__ . '/languages/';
        $domain = 'sociallymap-connect';
        $locale = get_locale();
        $path = $dir . $domain . '-' . $locale . '.mo';
        load_textdomain($domain, $path);
    }

    public function logsExport()
    {
        if (isset($_GET['export_all_posts'])) {
            BaseController::forward('LogController::logsDownload');
        }
    }

    public static function uninstallPlugin()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $entityRepository = new EntityRepository();
        $entityRepository->destroyTable();

        $messageRepository = new MessageRepository();
        $messageRepository->destroyTable();

        Logger::destroyTable();

        self::destroyConfig();
    }

    public static function upgradeSociallymapConnectPlugin()
    {
        global $wpdb;

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . OldTableNameConfig::ENTITIES . "'")) {

            $entityRepository = new EntityRepository();
            $messageRepository = new MessageRepository();

            $entityRepository->initTable();
            $messageRepository->initTable();

            $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . OldTableNameConfig::ENTITIES, ARRAY_A);
            foreach ($result as $oldEntity) {
                if (!$wpdb->get_results(
                    'SELECT * FROM ' .
                    $wpdb->prefix .
                    TableNameConfig::ENTITIES .
                    ' WHERE sm_entity_id=\'' .
                    $oldEntity['sm_entity_id'] .
                    '\''
                )) {
                    $entity = new Entity();
                    $entity->setSmEntityId($oldEntity['sm_entity_id']);
                    $entity->setEnabled($oldEntity['activate']);
                    $entity->setAuthorId($oldEntity['author_id']);
                    $entity->setName($oldEntity['name']);
                    $entity->setLastPublishedMessage(new \DateTime($oldEntity['last_published_message']));

                    $options =
                        $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . OldTableNameConfig::OPTIONS, ARRAY_A);
                    foreach ($options as $option) {
                        $optionValue = self::getOptionValue($option['id'], $oldEntity['id']);

                        switch ($option['label']) {
                            case 'category':
                                if ($optionValue) {
                                    foreach ($optionValue as $categoryId) {
                                        $entity->addCategoryId($categoryId['value']);
                                    }
                                }
                                break;
                            case 'display_type':
                                if ($optionValue === 'modal') {
                                    $entity->setDisplayInModal(true);
                                } else {
                                    $entity->setDisplayInModal(false);
                                }

                                break;
                            case 'publish_type':
                                if ($optionValue === Type::PUBLISH) {
                                    $type = new Type(Type::PUBLISH);
                                } elseif ($optionValue === Type::PENDING) {
                                    $type = new Type(Type::PENDING);
                                } elseif ($optionValue === Type::PRIVATE_POST) {
                                    $type = new Type(Type::PRIVATE_POST);
                                } else {
                                    $type = new Type(Type::DRAFT);
                                }
                                $entity->setPublicationType($type);
                                break;
                            case 'link_canonical':
                                $entity->setIncludeCanonicalLink((boolean)$optionValue);
                                break;
                            case 'image':
                                if ($optionValue === Image::CONTENT) {
                                    $image = new Image(Image::CONTENT);
                                } elseif ($optionValue === Image::THUMBNAIL) {
                                    $image = new Image(Image::THUMBNAIL);
                                } else {
                                    $image = new Image(Image::BOTH);
                                }
                                $entity->setImagePublicationType($image);
                                break;
                            case 'readmore_label':
                                $entity->setReadMoreLabel($optionValue);
                                break;
                            case 'noFollow':
                                $entity->setNoFollow((boolean)$optionValue);
                                break;
                            case 'balise more':
                                $entity->setReadMoreEnabled((boolean)$optionValue);
                                break;
                        }
                    }

                    $entity->setCreditImage(true);

                    $newEntityId = $entityRepository->persist($entity);

                    // Messages published
                    $messagesPublished =
                        $wpdb->get_results(
                            'SELECT * FROM ' .
                            $wpdb->prefix .
                            OldTableNameConfig::PUBLISHED .
                            ' WHERE entity_id=\'' .
                            $oldEntity['id'] .
                            '\'',
                            ARRAY_A
                        );

                    if ($messagesPublished) {
                        foreach ($messagesPublished as $messagePublished) {
                            $messageRepository->publishMessage(
                                $newEntityId,
                                $messagePublished['post_id'],
                                $messagePublished['message_id']
                            );
                        }
                    }
                }
            }

            $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . OldTableNameConfig::OPTIONS);
            $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . OldTableNameConfig::ENTITY_OPTIONS);
            $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . OldTableNameConfig::ENTITIES);
            $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . OldTableNameConfig::PUBLISHED);
        }
    }
}
$config = require __DIR__ . '/config.php';

register_activation_hook(__FILE__, ['SociallymapConnectPlugin', 'initPlugin']);

new SociallymapConnectPlugin($config);
