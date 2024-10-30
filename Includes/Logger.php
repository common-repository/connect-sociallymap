<?php

namespace SociallymapConnect\Includes;

use SociallymapConnect\Configs\Database\TableNameConfig;
use SociallymapConnect\Enums\Log\LogDriver;
use SociallymapConnect\Enums\Log\LogLevel;
use SociallymapConnect\Includes\Exceptions\LoggerException;
use SociallymapConnect\Includes\Exceptions\SmartEnumException;

class Logger
{
    /** @var string */
    private static $logDriver = LogDriver::NOLOG;

    /** @var bool */
    private static $databaseInitedFlag = false;

    /**
     * @param \DateTime $since
     */
    public static function cleanLogsInDatase(\DateTime $since)
    {
        global $wpdb;

        $tableName = self::getTableName($wpdb->prefix);

        $sql = 'DELETE FROM ' . $tableName . ' WHERE log_date <= "' . $since->format('Y-m-d H:i:s') . '"';

        $wpdb->query($sql);
    }

    /**
     * @param $prefix
     * @return string
     */
    private static function getTableName($prefix)
    {
        return $prefix . TableNameConfig::LOG;
    }

    /**
     * @param LogLevel $level
     * @param string   $message
     * @param array    $data
     * @throws SmartEnumException
     * @throws \ReflectionException
     */
    private static function log(LogLevel $level, $message, array $data = [])
    {
        if (!LogLevel::isValid($level)) {
            throw new SmartEnumException($level->__toString());
        }

        if (self::$logDriver === LogDriver::FILE_SYSTEM) {
            self::logToFilesystem($level, $message, $data);
        } elseif (self::$logDriver === LogDriver::DATABASE) {
            self::logToDatabase($level->getValue(), $message, $data);
        }
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $data
     */
    private static function logToDatabase($level, $message, array $data = [])
    {
        global $wpdb;
        $tableName = self::getTableName($wpdb->prefix);

        if (!self::$databaseInitedFlag) {
            self::initTable();
        }

        if (empty($data)) {
            $data = [
                'log_date' => date('c'),
                'log_level' => $level,
                'log_message' => $message
            ];
        }

        $format = ['%s', '%s', '%s'];
        $wpdb->insert($tableName, $data, $format);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $data
     */
    private static function logToFilesystem($level, $message, array $data = [])
    {
        $dirname = plugin_dir_path(__FILE__) . '../logs';
        if (!\file_exists($dirname) && !mkdir($dirname) && !is_dir($dirname)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dirname));
        }
        $filename = $dirname . '/' . $level . '.log';
        $logMessage = sprintf("\n[%s] %s: %s", date('c'), \strtoupper($level), $message);
        \file_put_contents($filename, $logMessage, FILE_APPEND);
    }

    public static function destroyTable()
    {
        global $wpdb;

        $tableName = self::getTableName($wpdb->prefix);

        $sql = 'DROP TABLE IF exists %s';
        $wpdb->query(sprintf($sql, $tableName));
    }

    /**
     * @return mixed
     */
    public static function findAll()
    {
        global $wpdb;

        $tableName = self::getTableName($wpdb->prefix);

        $sql = sprintf("SELECT * FROM %s", $tableName);

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * @param array $criteria
     * @param array $order
     * @return array
     */
    public static function findBy(array $criteria = [], array $order = [])
    {
        global $wpdb;

        $tableName = self::getTableName($wpdb->prefix);

        $sqlCriteria = '';
        $sqlOrderBy = '';

        if (!empty($order)) {
            $sqlOrderPart = [];
            foreach ($order as $orderKey => $orderSens) {
                $sqlOrderPart[] = $orderKey . ' ' . $orderSens;
            }
            $sqlOrderBy = 'ORDER BY ' . join(',', $sqlOrderPart);
        }

        if (!empty($criteria)) {
            $sqlCriteriaPart = [];
            foreach ($criteria as $fieldName => $value) {
                $sqlCriteriaPart[] = sprintf('%s = \'%s\'', $fieldName, $value);
            }
            $sqlCriteria = 'WHERE ' . join(' AND ', $sqlCriteriaPart);
        }

        $sql = sprintf(
            "SELECT * FROM %s %s %s", $tableName, $sqlCriteria, $sqlOrderBy
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * @return string
     */
    public static function getLogDriver()
    {
        return self::$logDriver;
    }

    public static function initTable()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        global $wpdb;

        $tableName = self::getTableName($wpdb->prefix);

        $charsetCollate = $wpdb->get_charset_collate();

        if (!$wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'")) {

            $sql = 'CREATE TABLE ' . $tableName . ' (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				log_date datetime,
				log_level varchar(30),
				log_message text,
				PRIMARY KEY(id)
				) ' . $charsetCollate . ';'
            ;

            dbDelta($sql);

            self::$databaseInitedFlag = true;
        }
    }

    /**
     * @param string $message
     * @param array  $data
     * @throws Exceptions\SmartEnumException
     * @throws \ReflectionException
     */
    public static function logDump($message, array $data = [])
    {
        $dump = new LogLevel(LogLevel::DUMP);

        self::log($dump, $message, $data);
    }

    /**
     * @param string $message
     * @param array  $data
     * @throws Exceptions\SmartEnumException
     * @throws \ReflectionException
     */
    public static function logError($message, array $data = [])
    {
        $error = new LogLevel(LogLevel::ERROR);

        self::log($error, $message, $data);
    }

    /**
     * @param string $message
     * @param array  $data
     * @throws Exceptions\SmartEnumException
     * @throws \ReflectionException
     */
    public static function logException($message, array $data = [])
    {
        $exception = new LogLevel(LogLevel::EXCEPTION);

        self::log($exception, $message, $data);
    }

    /**
     * @param string $message
     * @param array  $data
     * @throws Exceptions\SmartEnumException
     * @throws \ReflectionException
     */
    public static function logInfo($message, array $data = [])
    {
        $info = new LogLevel(LogLevel::INFO);

        self::log($info, $message, $data);
    }

    /**
     * @param string $message
     * @param array  $data
     * @throws SmartEnumException
     * @throws \ReflectionException
     */
    public static function logMessageReceived($message, array $data = [])
    {
        $messageReceived = new LogLevel(LogLevel::MESSAGE_RECEIVED);

        self::log($messageReceived, $message, $data);
    }

    /**
     * @param $logDriver
     * @throws LoggerException
     * @throws \ReflectionException
     */
    public static function setLogDriver($logDriver)
    {
        if (!LogDriver::isValid($logDriver)) {
            $errorMessage = sprintf('Log driver "%s" is not a built in driver', $logDriver);
            throw new LoggerException($errorMessage);
        }

        self::$logDriver = $logDriver;
    }
}
