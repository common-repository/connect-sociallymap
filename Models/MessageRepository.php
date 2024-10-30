<?php

namespace SociallymapConnect\Models;

use SociallymapConnect\Configs\Database\TableNameConfig;
use SociallymapConnect\Includes\Exceptions\SmartEnumException;
use SociallymapConnect\Includes\Logger;

class MessageRepository
{
    /**
     * @var string
     */
    private $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->tableName = $this->wpdb->prefix . TableNameConfig::PUBLISHED;
    }

    /**
     * @param $messageGuid
     * @param $entityId
     * @return bool
     * @throws \Exception
     */
    public function checkMessageAlreadyPublished($messageGuid, $entityId)
    {
        $entityRepository = new EntityRepository();

        /** @var Entity $entity */
        $entity = $entityRepository->findOneById($entityId);

        $sql = sprintf(
            'SELECT * FROM %s WHERE message_guid=\'%s\' AND entity_id=%d', $this->tableName, $messageGuid, $entityId
        );

        $tmpResult = $this->wpdb->get_results($sql, ARRAY_A);

        if ($this->wpdb->last_error) {
            $errorData['sql'] = $this->wpdb->last_query;
            $entity->increaseErrorCounter();
            Logger::logError($this->wpdb->last_error, $sql);
            return false;
        }

        return count($tmpResult) >= 1;
    }

    public function destroyTable()
    {
        $sql = 'DROP TABLE IF exists %s';
        $this->wpdb->query(sprintf($sql, $this->tableName));
    }

    public function initTable()
    {
        $charsetCollate = $this->wpdb->get_charset_collate();

        if (!$this->wpdb->get_var("SHOW TABLES LIKE '" . $this->tableName . "'")) {

            $sql = 'CREATE TABLE ' . $this->tableName . ' (
				entity_id mediumint(9),
				post_id mediumint(9),
				message_guid varchar(50),
				UNIQUE KEY(message_guid, entity_id)
				) ' . $charsetCollate . ';'
            ;

            dbDelta($sql);
        }
    }

    /**
     * @param $entityId
     * @param $postId
     * @param $messageGuid
     * @return bool
     * @throws \ReflectionException
     * @throws SmartEnumException
     */
    public function publishMessage($entityId, $postId, $messageGuid)
    {
        $data = [
            'entity_id' => $entityId,
            'post_id' => $postId,
            'message_guid' => $messageGuid
        ];
        $format = ['%d', '%d', '%s'];

        if (!$this->wpdb->insert($this->tableName, $data, $format)) {
            $errorData['sql'] = $this->wpdb->last_query;
            Logger::logError($this->wpdb->last_error, $errorData);
            return false;
        }

        return true;
    }

    /**
     * @param $messageGuid
     * @param $entityId
     * @param $postId
     * @return bool
     * @throws SmartEnumException
     * @throws \ReflectionException
     */
    public function publishPostMessage($messageGuid, $entityId, $postId)
    {
        if (!$this->checkMessageAlreadyPublished($messageGuid, $entityId)) {
            $format = ['%s', '%d', '%d'];
            $data = [
                'message_guid' => $messageGuid,
                'entity_id' => $entityId,
                'post_id' => $postId,
            ];

            if (!$this->wpdb->insert($this->tableName, $data, $format)) {
                $errorData['sql'] = $this->wpdb->last_query;
                Logger::logError($this->wpdb->last_error, $errorData);
                return false;
            }

            return true;
        }

        return false;
    }
}
