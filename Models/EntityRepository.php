<?php

namespace SociallymapConnect\Models;

use SociallymapConnect\Configs\Database\TableNameConfig;
use SociallymapConnect\Configs\EntityConfig;
use SociallymapConnect\Includes\Exceptions\EntityNotFoundException;
use SociallymapConnect\Includes\Exceptions\SmartEnumException;
use SociallymapConnect\Includes\Logger;

class EntityRepository
{
    /**
     * @var Wpdb
     */
    protected $wpdb;

    /**
     * @var array
     */
    protected $fieldsPool = [
        'id'                     => 'id',
        'sm_entity_id'           => 'smEntityId',
        'enabled'                => 'enabled',
        'counter'                => 'errorCounter',
        'author_id'              => 'authorId',
        'name'                   => 'name',
        'last_published_message' => 'lastPublishedMessage',
        'read_more_enabled'      => 'readMoreEnabled',
        'read_more_label'        => 'readMoreLabel',
        'image_publication_type' => 'imagePublicationType',
        'publication_type'       => 'publicationType',
        'display_in_modal'       => 'displayInModal',
        'include_canonical_link' => 'includeCanonicalLink',
        'compatibility_yoastseo' => 'compatibilityYoastSEO',
        'credit_image'           => 'creditImage',
        'no_follow'              => 'noFollow',
    ];

    /**
     * @var string
     */
    private $tableEntities;

    /**
     * @var string
     */
    private $tableCategories;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->tableEntities = $this->wpdb->prefix . TableNameConfig::ENTITIES;
        $this->tableCategories = $this->wpdb->prefix . TableNameConfig::CATEGORIES;
    }

    /**
     * @param $id
     * @return mixed
     */
    private function findOneEntityById($id)
    {
        $sql = sprintf('SELECT * FROM %s WHERE id="%s"', $this->tableEntities, $id);

        $entity = $this->wpdb->get_results($sql, ARRAY_A);

        return $entity[0];
    }

    /**
     * @param Entity $entity
     * @return bool
     * @throws \ReflectionException
     * @throws SmartEnumException
     */
    private function insertEntity(Entity $entity)
    {
        $data = $this->toDbArray($entity);
        $format = ['%s', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s'];

        if (!$this->wpdb->insert($this->tableEntities, $data, $format)) {
            $errorData['sql'] = $this->wpdb->last_query;
            Logger::logError($this->wpdb->last_error, $errorData);
            return false;
        }

        $entityId = $this->wpdb->insert_id;
        $entity->setId($entityId);

        foreach ($entity->getTargetCategoriesId() as $catId) {
            $data = [
                'entity_id'   => $entityId,
                'category_id' => $catId
            ];

            if (!$this->wpdb->insert($this->tableCategories, $data, ['%d', '%d'])) {
                $errorData['sql'] = $this->wpdb->last_query;
                Logger::logError($this->wpdb->last_error, $errorData);
                return false;
            }
        }

        return true;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function toDbArray(Entity $entity)
    {
        if ($entity->getLastPublishedMessage()) {
            $lastPublishedMessage = $entity->getLastPublishedMessage()->format('Y-m-d H:i:s');
        } else {
            $lastPublishedMessage = null;
        }

        $data = [
            'sm_entity_id'           => $entity->getSmEntityId(),
            'enabled'                => $entity->getEnabled(),
            'counter'                => $entity->getErrorCounter(),
            'author_id'              => $entity->getAuthorId(),
            'name'                   => $entity->getName(),
            'last_published_message' => $lastPublishedMessage,
            'read_more_enabled'      => $entity->getReadMoreEnabled(),
            'read_more_label'        => $entity->getReadMoreLabel(),
            'image_publication_type' => $entity->getImagePublicationType(),
            'publication_type'       => $entity->getPublicationType(),
            'display_in_modal'       => $entity->getDisplayInModal(),
            'include_canonical_link' => $entity->getIncludeCanonicalLink(),
            'compatibility_yoastseo' => $entity->getCompatibilityYoastSEO(),
            'credit_image'           => $entity->getCreditImage(),
            'no_follow'              => $entity->getNoFollow(),
        ];

        return $data;
    }

    /**
     * @param Entity $entity
     * @return bool
     * @throws SmartEnumException
     * @throws \ReflectionException
     */
    private function updateEntity(Entity $entity)
    {
        $data = $this->toDbArray($entity);
        $format = ['%s', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s'];

        if ($this->wpdb->update($this->tableEntities, $data, ['id' => $entity->getId()], $format, ['%s']) === false) {
            $errorData['sql'] = $this->wpdb->last_query;
            Logger::logError($this->wpdb->last_error, $errorData);
            return false;
        }

        $this->wpdb->delete(
            $this->tableCategories, ['entity_id' => $entity->getId()], ['%d']
        );

        foreach ($entity->getTargetCategoriesId() as $catId) {
            $data = [
                'entity_id' => $entity->getId(),
                'category_id' => $catId
            ];

            if (!$this->wpdb->insert($this->tableCategories, $data, ['%d', '%d'])) {
                $errorData['sql'] = $this->wpdb->last_query;
                Logger::logError($this->wpdb->last_error, $errorData);
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @param integer $mode
     * @return array
     */
    public function convertFieldName(array $data, $mode)
    {
        $pool = $this->fieldsPool;

        if ($mode === EntityConfig::CONVERT_FIELD_TO_DB) {
            $pool = array_flip($pool);
        }

        $result = [];
        foreach ($data as $key => $value) {
            //Ignore categories

            if (strpos($key, 'categories') === false) {
                $result[$pool[$key]] = $value;
            }
        }

        return $result;
    }

    public function destroyTable()
    {
        $sql = 'DROP TABLE IF exists %s';
        $this->wpdb->query(sprintf($sql, $this->tableEntities));
        $this->wpdb->query(sprintf($sql, $this->tableCategories));
    }

    /**
     * @param array $criteria
     * @param array $order
     * @return array|bool
     * @throws \Exception
     */
    public function findAll(array $criteria = [], array $order = [])
    {
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
            "SELECT * FROM %s %s %s", $this->tableEntities, $sqlCriteria, $sqlOrderBy
        );

        $tmpResult = $this->wpdb->get_results($sql, ARRAY_A);
        $result = [];
        if ($this->wpdb->last_error) {
            $errorData['sql'] = $this->wpdb->last_query;
            Logger::logError($this->wpdb->last_error);
            return false;
        }

        foreach ($tmpResult as $row) {
            $entity = Entity::createFromArray($this->convertFieldName($row, EntityConfig::CONVERT_FIELD_TO_OBJECT));

            $sqlCategories = sprintf(
                'SELECT * FROM %s WHERE entity_id="%s"', $this->tableCategories, $row['id']
            );

            $tmpCategories = $this->wpdb->get_results($sqlCategories, ARRAY_A);
            foreach ($tmpCategories as $value) {
                $entity->addCategoryId($value['category_id']);
            }
            $result[] = $entity;
        }

        return $result;
    }

    private function findCategoriesByEntityId($entityId)
    {
        $categories = $this->wpdb->get_results(
            sprintf('SELECT * FROM %s WHERE entity_id="%s"', $this->wpdb->prefix . TableNameConfig::CATEGORIES, $entityId), ARRAY_A);

        return $categories;
    }

    /**
     * @param string $entityId
     * @return mixed
     * @throws \Exception
     */
    public function findOneByEntityId($entityId)
    {
        $result = $this->wpdb->get_results(
            sprintf('SELECT * FROM %s WHERE sm_entity_id="%s" LIMIT 1', $this->wpdb->prefix . TableNameConfig::ENTITIES, $entityId));

        if (empty($result)) {
            throw new EntityNotFoundException();
        }
        $entity = Entity::createFromObject($result[0]);

        $categories = $this->findCategoriesByEntityId($entity->getId());

        foreach ($categories as $category) {
            $entity->addCategoryId($category['category_id']);
        }

        return $entity;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function findOneById($id)
    {
        $result = $this->wpdb->get_results(
            sprintf('SELECT * FROM %s WHERE id="%s" LIMIT 1', $this->wpdb->prefix . TableNameConfig::ENTITIES, $id));

        $entity = isset($result) ? Entity::createFromObject($result[0]) : null;

        $categories = $this->findCategoriesByEntityId($entity->getId());

        foreach ($categories as $category) {
            $entity->addCategoryId($category['category_id']);
        }

        return $entity;
    }

    public function initTable()
    {
        if (!function_exists('dbDelta')){
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $charsetCollate = $this->wpdb->get_charset_collate();

        if (!$this->wpdb->get_var("SHOW TABLES LIKE '" . $this->tableEntities . "'")) {

            $sql = 'CREATE TABLE ' . $this->tableEntities . ' (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				sm_entity_id varchar(30),
				enabled boolean,
				author_id varchar(255),
				name varchar(255),
				counter integer DEFAULT 0,
				last_published_message datetime,
				read_more_enabled boolean,
				read_more_label varchar(255),
				image_publication_type varchar(255),
				publication_type varchar(255),
				display_in_modal boolean,
				include_canonical_link boolean,
				compatibility_yoastseo boolean,
				credit_image boolean,
				no_follow boolean,
				PRIMARY KEY(id),
				UNIQUE KEY (sm_entity_id)
				) ' . $charsetCollate . ';'
            ;

            dbDelta($sql);
        }

        if (!$this->wpdb->get_var("SHOW TABLES LIKE '" . $this->tableCategories . "'")) {

            $sql = 'CREATE TABLE ' . $this->tableCategories . ' (
				entity_id mediumint(9),
				category_id mediumint(9),
				UNIQUE KEY (category_id, entity_id)
				) ' . $charsetCollate . ';'
            ;

            dbDelta($sql);
        }
    }

    /**
     * @param Entity $entity
     * @return boolean
     * @throws SmartEnumException
     * @throws \ReflectionException
     */
    public function persist(Entity &$entity)
    {
        if (null === $entity->getId()) {
            return $this->insertEntity($entity);
        }

        return $this->updateEntity($entity);
    }

    /**
     * @param $id
     * @return array
     */
    public function removeById($id)
    {
        $entity = $this->findOneEntityById($id);

        $this->wpdb->delete($this->tableEntities, ['id' => $id], ['%d']);
        $this->wpdb->delete($this->tableCategories, ['entity_id' => $id], ['%d']);

        return $entity;
    }
}
