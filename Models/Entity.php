<?php

namespace SociallymapConnect\Models;

use SociallymapConnect\Configs\PluginConfig;
use SociallymapConnect\Enums\Publication\Image;
use SociallymapConnect\Enums\Publication\Type;
use SociallymapConnect\Includes\Exceptions\SmartEnumException;
use SociallymapConnect\Includes\Logger;

class Entity
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $smEntityId;

    /**
     * @var boolean
     */
    protected $enabled = false;

    /**
     * @var integer
     */
    protected $errorCounter = 0;

    /**
     * @var string
     */
    protected $authorId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \DateTime
     */
    protected $lastPublishedMessage;

    /**
     * @var boolean
     */
    protected $readMoreEnabled = false;

    /**
     * @var string
     */
    protected $readMoreLabel;

    /**
     * @var array
     */
    protected $targetCategoriesId = [];

    /**
     * @var string
     */
    protected $imagePublicationType;

    /**
     * @var boolean
     */
    protected $displayInModal = false;

    /**
     * @var boolean
     */
    protected $includeCanonicalLink = false;

    /**
     * @var boolean
     */
    protected $noFollow = false;

    /**
     * @var boolean
     */
    protected $publicationType;

    /**
     * @var boolean
     */
    protected $compatibilityYoastSEO;

    /**
     * @var boolean
     */
    protected $creditImage;

    /**
     * @param integer $categoryId
     * @return self
     */
    public function addCategoryId($categoryId)
    {
        if (!in_array($categoryId, $this->getTargetCategoriesId(), true)) {
            $this->targetCategoriesId[] = $categoryId;
        }

        return $this;
    }

    /**
     * @param array $arrayRef
     * @return Entity
     * @throws \Exception
     */
    public static function createFromArray(array $arrayRef)
    {
        $entity = new Entity();

        if (\array_key_exists('id', $arrayRef)) {
            $entity->setId((integer) $arrayRef['id']);
        }
        if (\array_key_exists('smEntityId', $arrayRef)) {
            $entity->setSmEntityId($arrayRef['smEntityId']);
        }
        if (\array_key_exists('enabled', $arrayRef)) {
            $entity->setEnabled((boolean) $arrayRef['enabled']);
        }
        if (\array_key_exists('errorCounter', $arrayRef)) {
            $entity->setErrorCounter($arrayRef['errorCounter']);
        }
        if (\array_key_exists('readMoreEnabled', $arrayRef)) {
            $entity->setReadMoreEnabled((boolean) $arrayRef['readMoreEnabled']);
        }
        if (\array_key_exists('readMoreLabel', $arrayRef)) {
            $entity->setReadMoreLabel($arrayRef['readMoreLabel']);
        }
        if (\array_key_exists('imagePublicationType', $arrayRef)) {
            $entity->setImagePublicationType($arrayRef['imagePublicationType']);
        }
        if (\array_key_exists('displayInModal', $arrayRef)) {
            $entity->setDisplayInModal((boolean) $arrayRef['displayInModal']);
        }
        if (\array_key_exists('includeCanonicalLink', $arrayRef)) {
            $entity->setIncludeCanonicalLink((boolean) $arrayRef['includeCanonicalLink']);
        }
        if (\array_key_exists('compatibilityYoastSEO', $arrayRef)) {
            $entity->setCompatibilityYoastSEO((boolean) $arrayRef['compatibilityYoastSEO']);
        }
        if (\array_key_exists('creditImage', $arrayRef)) {
            $entity->setCreditImage((boolean) $arrayRef['creditImage']);
        }
        if (\array_key_exists('publicationType', $arrayRef)) {
            $entity->setPublicationType($arrayRef['publicationType']);
        }
        if (\array_key_exists('noFollow', $arrayRef)) {
            $entity->setNoFollow((boolean) $arrayRef['noFollow']);
        }
        if (\array_key_exists('authorId', $arrayRef)) {
            $entity->setAuthorId((integer) $arrayRef['authorId']);
        }
        if (\array_key_exists('name', $arrayRef)) {
            $entity->setName($arrayRef['name']);
        }
        if (\array_key_exists('lastPublishedMessage', $arrayRef)) {
            if ($arrayRef['lastPublishedMessage'] !== null) {
                $lastPublishedMessageDate = new \DateTime($arrayRef['lastPublishedMessage']);
                $entity->setLastPublishedMessage($lastPublishedMessageDate);
            }
        }
        if (\array_key_exists('categories', $arrayRef)) {
            foreach ($arrayRef['categories'] as $categoryId) {
                $entity->addCategoryId($categoryId);
            }
        }
        return $entity;
    }

    /**
     * @param \stdClass $objectRef
     * @return Entity
     * @throws \Exception
     */
    public static function createFromObject(\stdClass $objectRef)
    {
        $entity = new Entity();

        if (isset($objectRef->id)) {
            $entity->setId((integer) $objectRef->id);
        }
        if (isset($objectRef->sm_entity_id)) {
            $entity->setSmEntityId($objectRef->sm_entity_id);
        }
        if (isset($objectRef->enabled)) {
            $entity->setEnabled((boolean) $objectRef->enabled);
        }
        if (isset($objectRef->counter)) {
            $entity->setErrorCounter($objectRef->counter);
        }
        if (isset($objectRef->read_more_enabled)) {
            $entity->setReadMoreEnabled((boolean) $objectRef->read_more_enabled);
        }
        if (isset($objectRef->read_more_label)) {
            $entity->setReadMoreLabel($objectRef->read_more_label);
        }
        if (isset($objectRef->image_publication_type)) {
            $entity->setImagePublicationType($objectRef->image_publication_type);
        }
        if (isset($objectRef->display_in_modal)) {
            $entity->setDisplayInModal((boolean) $objectRef->display_in_modal);
        }
        if (isset($objectRef->include_canonical_link)) {
            $entity->setIncludeCanonicalLink((boolean) $objectRef->include_canonical_link);
        }
        if (isset($objectRef->compatibility_yoastseo)) {
            $entity->setCompatibilityYoastSEO((boolean) $objectRef->compatibility_yoastseo);
        }
        if (isset($objectRef->credit_image)) {
            $entity->setCreditImage((boolean) $objectRef->credit_image);
        }
        if (isset($objectRef->publication_type)) {
            $entity->setPublicationType($objectRef->publication_type);
        }
        if (isset($objectRef->no_follow)) {
            $entity->setNoFollow((boolean) $objectRef->no_follow);
        }
        if (isset($objectRef->author_id)) {
            $entity->setAuthorId((integer) $objectRef->author_id);
        }
        if (isset($objectRef->name)) {
            $entity->setName($objectRef->name);
        }
        if (isset($objectRef->last_published_message)) {
            if ($objectRef->last_published_message !== null) {
                $lastPublishedMessageDate = new \DateTime($objectRef->last_published_message);
                $entity->setLastPublishedMessage($lastPublishedMessageDate);
            }
        }
        if (isset($objectRef->categories)) {
            foreach ($objectRef->categories as $categoryId) {
                $entity->addCategoryId($categoryId);
            }
        }
        return $entity;
    }

    /**
     * @return self
     */
    public function increaseErrorCounter()
    {
        ++$this->errorCounter;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @return  boolean
     */
    public function getDisplayInModal()
    {
        return $this->displayInModal;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return integer
     */
    public function getErrorCounter()
    {
        return $this->errorCounter;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $errors = [];

        if (empty($this->getSmEntityId())) {
            $errors['smEntityId'] = __('L\'identifiant de l\'entité wordpress de Sociallymap doit être renseigné', PluginConfig::DOMAIN_TRANSLATE);
        }

        if (empty($this->getName())) {
            $errors['name'] = __('Le nom de l\'entité wordpress de Sociallymap doit être renseigné', PluginConfig::DOMAIN_TRANSLATE);
        }

        return $errors;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return  string
     */
    public function getImagePublicationType()
    {
        return $this->imagePublicationType;
    }

    /**
     * @return  boolean
     */
    public function getIncludeCanonicalLink()
    {
        return $this->includeCanonicalLink;
    }

    /**
     * @return \DateTime
     */
    public function getLastPublishedMessage()
    {
        return $this->lastPublishedMessage;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return  boolean
     */
    public function getNoFollow()
    {
        return $this->noFollow;
    }

    /**
     * @return  boolean
     */
    public function getPublicationType()
    {
        return $this->publicationType;
    }

    /**
     * @return  boolean
     */
    public function getReadMoreEnabled()
    {
        return $this->readMoreEnabled;
    }

    /**
     * @return  string
     */
    public function getReadMoreLabel()
    {
        return htmlspecialchars_decode($this->readMoreLabel);
    }

    /**
     * @return string
     */
    public function getSmEntityId()
    {
        return $this->smEntityId;
    }

    /**
     * @return  array
     */
    public function getTargetCategoriesId()
    {
        return $this->targetCategoriesId;
    }

    /**
     * @return $this
     */
    public function resetErrorCounter()
    {
        $this->errorCounter = 0;

        return $this;
    }

    /**
     * @param  integer  $authorId
     * @return  self
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

        return $this;
    }

    /**
     * @param  boolean  $displayInModal
     * @return  self
     */
    public function setDisplayInModal($displayInModal)
    {
        $this->displayInModal = $displayInModal;

        return $this;
    }

    /**
     * @param  boolean  $enabled
     * @return  self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param  integer $errorCounter
     * @return  self
     */
    public function setErrorCounter($errorCounter)
    {
        $this->errorCounter = $errorCounter;

        return $this;
    }

    /**
     * @param  integer  $id
     * @return  self
     */
    public function setId($id)
    {
        $this->id = (integer) $id;

        return $this;
    }

    /**
     * @param string $imagePublicationType
     * @return $this
     * @throws \Exception
     */
    public function setImagePublicationType($imagePublicationType)
    {
        if (!in_array($imagePublicationType, Image::getValues(), true)) {
            $messageTemplate = 'Invalid value %s for property imagePublicationType for entity %s';
            $message = sprintf($messageTemplate, $imagePublicationType, $this->getSmEntityId());
            Logger::logError($message);
            throw new \Exception($message);
        }

        $this->imagePublicationType = $imagePublicationType;

        return $this;
    }

    /**
     * @param  boolean  $includeCanonicalLink
     * @return  self
     */
    public function setIncludeCanonicalLink($includeCanonicalLink)
    {
        $this->includeCanonicalLink = $includeCanonicalLink;

        return $this;
    }

    /**
     * @param  \DateTime  $lastPublishedMessage
     * @return  self
     */
    public function setLastPublishedMessage(\DateTime $lastPublishedMessage)
    {
        $this->lastPublishedMessage = $lastPublishedMessage;

        return $this;
    }

    /**
     * @param  string  $name
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  boolean  $noFollow
     * @return  self
     */
    public function setNoFollow($noFollow)
    {
        $this->noFollow = $noFollow;

        return $this;
    }

    /**
     * @param string $publicationType
     * @return  self
     * @throws \ReflectionException
     * @throws SmartEnumException
     */
    public function setPublicationType($publicationType)
    {
        if (!in_array($publicationType, Type::getValues(), true)) {
            $messageTemplate = 'Invalid value %s for property publicationType for entity %s';
            $errorMessage = sprintf($messageTemplate, $publicationType, $this->getSmEntityId());
            Logger::logError($errorMessage);
        }

        $this->publicationType = $publicationType;

        return $this;
    }

    /**
     * @param  boolean  $readMoreEnabled
     * @return  self
     */
    public function setReadMoreEnabled($readMoreEnabled)
    {
        $this->readMoreEnabled = $readMoreEnabled;

        return $this;
    }

    /**
     * @param  string  $readMoreLabel
     * @return  self
     */
    public function setReadMoreLabel($readMoreLabel)
    {
        $this->readMoreLabel = htmlspecialchars($readMoreLabel);

        return $this;
    }

    /**
     * @param string $smEntityId
     * @return  self
     */
    public function setSmEntityId($smEntityId)
    {
        $this->smEntityId = $smEntityId;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCompatibilityYoastSEO()
    {
        return $this->compatibilityYoastSEO;
    }

    /**
     * @param bool $compatibilityYoastSEO
     * @return Entity
     */
    public function setCompatibilityYoastSEO($compatibilityYoastSEO)
    {
        $this->compatibilityYoastSEO = $compatibilityYoastSEO;
        return $this;
    }

    /**
     * @return bool
     */
    public function getCreditImage()
    {
        return $this->creditImage;
    }

    /**
     * @param bool $creditImage
     * @return Entity
     */
    public function setCreditImage($creditImage)
    {
        $this->creditImage = $creditImage;
        return $this;
    }

    public function toArray($withoutId = false)
    {
        if (null !== $this->getLastPublishedMessage()) {
            $this->getLastPublishedMessage()->format('Y-m-d H:i:s');
        }

        $arrayResult = [
            'smEntityId'            => $this->getSmEntityId(),
            'enabled'               => $this->getEnabled(),
            'errorCounter'          => $this->getErrorCounter(),
            'readMoreEnabled'       => $this->getReadMoreEnabled(),
            'readMoreLabel'         => $this->getReadMoreLabel(),
            'targetCategoriesId'    => $this->getTargetCategoriesId(),
            'imagePublicationType'  => $this->getImagePublicationType(),
            'displayInModal'        => $this->getDisplayInModal(),
            'includeCanonicalLink'  => $this->getIncludeCanonicalLink(),
            'compatibilityYoastSEO' => $this->getCompatibilityYoastSEO(),
            'creditImage'           => $this->getCreditImage(),
            'publicationType'       => $this->getPublicationType(),
            'noFollow'              => $this->getNoFollow(),
            'authorId'              => $this->getAuthorId(),
            'name'                  => $this->getName(),
            'lastPublishedMessage'  => $this->getLastPublishedMessage(),
        ];

        if (!$withoutId) {
            $arrayResult['id'] = $this->getId();
        }

        return $arrayResult;
    }

    public function toArrayWithoutId()
    {
        return $this->toArray(true);
    }
}
