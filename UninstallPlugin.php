<?php

namespace SociallymapConnect;

use SociallymapConnect\Includes\Logger;
use SociallymapConnect\Models\EntityRepository;
use SociallymapConnect\Models\MessageRepository;

class UninstallPlugin {

    static function uninstallPlugin() {

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        if (!defined('WP_UNINSTALL_PLUGIN')) {
            die;
        }

        $entityRepository = new EntityRepository();
        $entityRepository->destroyTable();

        $messageRepository = new MessageRepository();
        $messageRepository->destroyTable();

        Logger::destroyTable();

        global $wpdb;

        $tableName = $wpdb->prefix . 'options';

        $wpdb->delete($tableName, ['option_name' => 'smc_request-secure']);
    }
}

