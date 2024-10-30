<?php

namespace SociallymapConnect\Controllers;

use SociallymapConnect\Configs\Database\TableNameConfig;
use SociallymapConnect\Configs\PluginConfig;
use SociallymapConnect\Configs\SupportedPlugin\YoastConfig;
use SociallymapConnect\Enums\ErrorMessage;
use SociallymapConnect\Enums\Language;
use SociallymapConnect\Enums\Publication\Image;
use SociallymapConnect\Includes\Errors\MessageAlreadyPublishedError;
use SociallymapConnect\Includes\Exceptions\EntityDisabledException;
use SociallymapConnect\Includes\Exceptions\Error500Exception;
use SociallymapConnect\Includes\Exceptions\SmartEnumException;
use SociallymapConnect\Includes\Requester;
use SociallymapConnect\Includes\Logger;
use SociallymapConnect\Models\Message;
use SociallymapConnect\Models\MessageRepository;
use SociallymapConnect\Models\Entity;
use SociallymapConnect\Models\EntityRepository;
use SociallymapConnect\Services\ExceptionHandler;
use SociallymapConnect\ValueObject\Media;
use SociallymapConnectPlugin;

include_once ABSPATH . 'wp-admin/includes/plugin.php';

class PluginController extends BaseController
{
    /**
     * @param array     $message
     * @param Entity    $entity
     * @param Requester $requester
     * @return MessageAlreadyPublishedError|Message
     * @throws \Exception
     */
    private function createMessageToPublishFromArray($message, $entity, $requester)
    {
        $messageToPublish = new Message();
        $messageRepository = new MessageRepository();

        $messageGuid = $message['guid'];
        $messageAlreadyPublished = $messageRepository->checkMessageAlreadyPublished($messageGuid, $entity->getId());
        if ($messageAlreadyPublished) {
            $errorMessage = sprintf(
                'Message #%s on entity #%s is already published.',
                $messageGuid,
                $entity->getSmEntityId()
            );

            return new MessageAlreadyPublishedError(ErrorMessage::ALREADY_PUBLISH, $errorMessage);
        }

        $messageToPublish->setGuid($messageGuid);
        if (array_key_exists('link', $message)) {
            if (!empty($message['link']['title'])) {
                $messageToPublish->setTitle($message['link']['title']);
            }

            if (!empty($message['link']['summary'])) {
                $messageToPublish->setContent($message['link']['summary']);
            }

            if (!empty($message['link']['url'])) {
                $messageToPublish->setUrl(html_entity_decode($message['link']['url']));
            }
        }
        if (!empty($message['content'])) {
            $messageToPublish->setContent($message['content']);
        }

        if (array_key_exists('medias', $message) && count($message['medias'])) {
            Logger::logInfo(
                sprintf('Message media url -> %s | Type -> %s', $message['medias'][0]['url'], $message['medias'][0]['type'])
            );
            if ($message['medias'][0]['type'] === 'photo') {
                $downloadedFilename = $requester->download($message['medias'][0]['url']);
                $savedMedia = $this->saveMediaToWordpress($downloadedFilename, \SociallymapConnect\Enums\Media::PHOTO);
                $messageToPublish->setMedia($savedMedia);
            } elseif (!empty(trim($message['link']['thumbnail']))) {
                $downloadedFilename = $requester->download($message['link']['thumbnail']);
                $savedMedia = $this->saveMediaToWordpress($downloadedFilename, \SociallymapConnect\Enums\Media::THUMBNAIL);
                $messageToPublish->setMedia($savedMedia);
            }

            if ($message['medias'][0]['type'] === 'video') {
                $downloadedFilename = $requester->download($message['medias'][0]['url']);
                $savedMedia = $this->saveMediaToWordpress($downloadedFilename, \SociallymapConnect\Enums\Media::VIDEO);
                $messageToPublish->setVideo($savedMedia['url']);
            }
        }

        return $messageToPublish;
    }

    /**
     * @param Message $messageToPublish
     * @param Entity  $entity
     * @return Message
     */
    private function formatPost(Message $messageToPublish, Entity $entity)
    {
        if ($messageToPublish->getMedia() !== null && $messageToPublish->getMedia()->getUrl()) {
            $imageTag = self::render(
                'partial/image',
                [
                    'src' => $messageToPublish->getMedia()->getUrl(),
                ]
            );

            if (in_array($entity->getImagePublicationType(), [Image::CONTENT, Image::BOTH], true)) {
                $messageToPublish->setContent($imageTag . $messageToPublish->getContent());
            }
            if (in_array($entity->getImagePublicationType(), [Image::THUMBNAIL, Image::BOTH], true)) {
                $messageToPublish->setAttachment($messageToPublish->getMedia()->getUrl());
            }
        }

        if ($entity->getCreditImage()) {
            $url = parse_url($messageToPublish->getUrl());

            $creditImage = self::render(
                'partial/imageCredit',
                [
                    'creditImage' => $url['host'],
                ]
            );
            $messageToPublish->setContent($messageToPublish->getContent() . $creditImage);
        }

        if ($messageToPublish->getVideo()) {
            $videoTag = self::render(
                'partial/video',
                [
                    'src' => $messageToPublish->getVideo(),
                ]
            );

            $messageToPublish->setContent($messageToPublish->getContent() . $videoTag);
        }

        if ($entity->getReadMoreEnabled() && $messageToPublish->getUrl()) {
            $readMore = self::render(
                'partial/readmore',
                [
                    'entityId'       => $entity->getId(),
                    'url'            => $messageToPublish->getUrl(),
                    'label'          => $entity->getReadMoreLabel(),
                    'displayInModal' => $entity->getDisplayInModal(),
                    'noFollow'       => $entity->getNoFollow(),
                ]
            );

            $messageToPublish->setContent($messageToPublish->getContent() . $readMore);
        }

        return $messageToPublish;
    }

    /**
     * @return array
     */
    private function getAuthorList()
    {
        $authorList = [];

        $wpQuery = new \WP_User_Query(['who' => 'authors']);
        $authors = $wpQuery->get_results();
        if (!empty($authors)) {
            foreach ($authors as $author) {
                $authorInfo = get_userdata($author->ID);
                $authorList[] = [
                    'id'          => $author->ID,
                    'displayName' => $authorInfo->display_name,
                ];
            }
        }

        return $authorList;
    }

    /**
     * @param Message $messageToPublish
     * @param Entity  $entity
     * @return int|\WP_Error
     * @throws \Exception
     */
    private function publishMessage(Message $messageToPublish, Entity $entity)
    {
        $messageFormatted = $this->formatPost($messageToPublish, $entity);
        $entityRepository = new EntityRepository();
        $post = [
            'post_title'    => $messageFormatted->getTitle(),
            'post_content'  => $messageFormatted->getContent(),
            'post_category' => $entity->getTargetCategoriesId(),
            'post_status'   => $entity->getPublicationType(),
            'post_author'   => $entity->getAuthorId(),
        ];

        remove_filter('content_save_pre', 'wp_filter_post_kses');
        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');


        $newPostId = wp_insert_post($post);
        if (is_wp_error($newPostId)) {
            return $newPostId;
        }

        if (!$newPostId) {
            $errorMessage = sprintf(
                'An error occured while saving post "%s" on entity #%s',
                $messageFormatted->getTitle(),
                $entity->getSmEntityId()
            );
            Logger::logError($errorMessage);

            $entity->setLastPublishedMessage(new \DateTime());
            $entity->increaseErrorCounter();
            $entityRepository->persist($entity);

            return $errorMessage;
        }

        $messageRepository = new MessageRepository();
        $messageRepository->publishMessage($entity->getId(), $newPostId, $messageFormatted->getGuid());

        if ($entity->getIncludeCanonicalLink() && $messageToPublish->getUrl()) {
            if ($entity->getCompatibilityYoastSEO()) {
                if (SociallymapConnectPlugin::checkPluginsState(YoastConfig::FILE)) {
                    update_post_meta($newPostId, YoastConfig::POST_META, $messageToPublish->getUrl());
                } else {
                    $messageLog =
                        sprintf('Publish with compatibility %s but that plugin isn\'t enabled', YoastConfig::NAME);
                    Logger::logError($messageLog);
                    add_post_meta($newPostId, TableNameConfig::SMC_POST_META, $messageToPublish->getUrl());
                }
            } else {
                add_post_meta($newPostId, TableNameConfig::SMC_POST_META, $messageToPublish->getUrl());
            }
        }

        $image = $messageFormatted->getMedia();
        if ($image !== null) {
            $attachment_data = \wp_generate_attachment_metadata($image->getId(), $image->getUrl());
            \wp_update_attachment_metadata($image->getId(), $attachment_data);
            \set_post_thumbnail($newPostId, $image->getId());
        }

        add_filter('content_save_pre', 'wp_filter_post_kses');
        add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

        $this->publishMessageWithSuccess($entity, $entityRepository, $messageFormatted);

        return $newPostId;
    }

    /**
     * @param Entity           $entity
     * @param EntityRepository $entityRepository
     * @param Message          $messageFormatted
     * @throws \Exception
     */
    private function publishMessageWithSuccess(Entity $entity, EntityRepository $entityRepository, Message $messageFormatted)
    {
        Logger::logInfo(
            sprintf(
                'Message published #%s in entity #%s',
                $messageFormatted->getGuid(),
                $entity->getSmEntityId()
            )
        );

        $oldValueErrorCounter = $entity->getErrorCounter();

        if ($oldValueErrorCounter > 0) {
            $entity->resetErrorCounter();
            Logger::logInfo(sprintf('Error counter was reseted (old value : %s)', $oldValueErrorCounter));
            $entityRepository->persist($entity);
        }

        $entity->setLastPublishedMessage(new \DateTime());
        $entityRepository->persist($entity);
    }

    /**
     * @param        $filename
     * @param string $type
     * @return false|Media
     * @throws \ReflectionException
     * @throws SmartEnumException
     */
    private function saveMediaToWordpress($filename, $type)
    {
        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin' . '/includes/image.php';
            require_once ABSPATH . 'wp-admin' . '/includes/file.php';
            require_once ABSPATH . 'wp-admin' . '/includes/media.php';
        }

        $file_array = [
            'name'     => \pathinfo($filename, PATHINFO_BASENAME),
            'tmp_name' => $filename,
        ];

        $response = media_handle_sideload($file_array, 0);
        if ($response instanceof \WP_Error) {
            $errorMessage = $response->get_error_message($response->get_error_code());
            Logger::logError($errorMessage);
        } else {
            Logger::logInfo(sprintf('Media associated with the post # %s', $response));
        }

        $url = wp_get_attachment_url($response);
        if (!$url) {
            $errorMessage = 'Media seems not exists. Can\'t get attachment url while saving media to wordpress.';
            Logger::logError($errorMessage);
        }

        return new Media($response, $url, $type);

    }

    /**
     * @param string $entityId
     * @return void
     * @throws \Exception
     */
    public function connectionTest($entityId)
    {
        global $wp_version;

        $entityRepository = new EntityRepository();
        try {
            $entity = $entityRepository->findOneByEntityId($entityId);
        } catch (\Exception $exception) {
            ExceptionHandler::handleException($exception);
        }

        if (!$entity->getEnabled()) {
            $errorMessage =
                sprintf(__('L\'entité #%s est désactivée dans wordpress', PluginConfig::DOMAIN_TRANSLATE), $entityId);
            throw new EntityDisabledException($errorMessage);
        }

        $curlVersion = curl_version();

        $response = [
            'message'           => 'ok',
            'curl_version'      => $curlVersion['version'],
            'sslVersion'        => $curlVersion['ssl_version'],
            'wordpressVersion'  => $wp_version,
            'phpVersion'        => PHP_VERSION,
            'disabledFunctions' => ini_get('disable_functions'),
        ];

        header('HTTP/1.0 200 OK');
        header('Content-Type: application/json');
        echo \json_encode($response);
        exit();
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function diagnostic()
    {
//        Cast in integer because the Wordpress database, "option_value" field, is in string
        if (isset($_POST['insecure'])) {
            DiagnosticController::updateRequestSecure((int)filter_var($_POST['insecure'], FILTER_VALIDATE_BOOLEAN));
        }

        $data = [
            'serverInfos' => DiagnosticController::getServerInfos(),
            'insecure'    => DiagnosticController::getRequestSecure(),
            'phpInfos'    => DiagnosticController::getPhpInfos(),
        ];

        return self::render('diagnostic', $data);
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function documentation()
    {
        $data = [];
        $locale = explode('_', get_locale());

        $page = 'documentation_fr';

        if (in_array($locale[0], Language::getValues())) {
            $page = 'documentation_' . $locale[0];
        }

        return self::render($page, $data);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function entityAdd()
    {
        $errors = [];
        if (isset($_POST['form_entity_add_sent'], $_POST['entity'])) {
            $entity = Entity::CreateFromArray($_POST['entity']);
            $errors = $entity->getErrors();

            if (empty($errors)) {
                $entityRepository = new EntityRepository();
                if ($entityRepository->persist($entity)) {
                    Logger::logInfo(
                        sprintf(
                            'Entity created: Widget ID -> %s | Name -> %s',
                            $entity->getSmEntityId(),
                            $entity->getName()
                        )
                    );
                    BaseController::redirectAdmin('sociallymap-entity-list');
                } else {
                    $errors['dbError'] = true;
                }
            }
        } else {
            $entity = new Entity();
        }

        $data = [
            'formData' => [
                'entityData' => $entity->toArrayWithoutId(),
                'errorsData' => $errors,
                'authorList' => $this->getAuthorList(),
                'onError'    => !empty($errors),
            ],
        ];

        return self::render('entity-add', $data);
    }

    public function entityDelete()
    {
        $entityRepository = new EntityRepository();
        $entity = $entityRepository->removeById($_REQUEST['id']);

        Logger::logInfo(
            sprintf('Entity #%s a was removed. Associated with SMWidget %s', $entity['id'], $entity['sm_entity_id'])
        );
        BaseController::redirectAdmin('sociallymap-entity-list');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function entityEdit()
    {
        $entityRepository = new EntityRepository();
        $id = $_REQUEST['id'];
        $errors = [];
        $_POST = array_map('stripslashes_deep', $_POST);
        if (isset($_POST['form_entity_edit_sent'], $_POST['entity'])) {
            $entity = Entity::CreateFromArray($_POST['entity']);
            $oldEntity = $entityRepository->findOneById($id);

            if (null !== $oldEntity->getLastPublishedMessage()) {
                $entity->setLastPublishedMessage($oldEntity->getLastPublishedMessage());
            }

            $errors = $entity->getErrors();

            if (empty($errors)) {
                if ($entityRepository->persist($entity)) {
                    Logger::logInfo(
                        sprintf(
                            'Entity updated : Widget ID -> %s | Name -> %s',
                            $entity->getSmEntityId(),
                            $entity->getName()
                        )
                    );
                    BaseController::redirectAdmin('sociallymap-entity-list');
                } else {
                    $errors['dbError'] = true;
                }
            }
        } else {
            $entity = $entityRepository->findOneById($id);
        }

        $data = [
            'formData' => [
                'entityData' => $entity->toArray(),
                'errorsData' => $errors,
                'authorList' => $this->getAuthorList(),
                'onError'    => !empty($errors),
            ],
        ];

        return self::render('entity-edit', $data);
    }

    /**
     * @param $entityId
     * @param $token
     * @param $environment
     * @throws Error500Exception
     * @throws \Exception
     */
    public function getSociallymapMessages($entityId, $token, $environment)
    {
        $requester = new Requester($environment);
        $entityRepository = new EntityRepository();

        try {
            /** @var Entity $entity */
            $entity = $entityRepository->findOneByEntityId($entityId);
        } catch (\Exception $exception) {
            ExceptionHandler::handleException($exception);
        }

        $responses = [
            'published' => 0,
            'skipped'   => [],
            'errors'    => [],
        ];

        $secureRequest = DiagnosticController::getRequestSecure();
        try {
            $messages = $requester->getMessages($entityId, $token, $secureRequest);
        } catch (\Exception $exception) {
            $entity->increaseErrorCounter();
            Logger::logError(sprintf(
                'Error when getting messages #%s : %s',
                $exception->getCode(),
                $exception->getMessage())
            );
            ExceptionHandler::handleException($exception);
        }

        foreach ($messages as $message) {

            $messageToPublish = $this->createMessageToPublishFromArray($message, $entity, $requester);
            if (is_wp_error($messageToPublish)) {
                $responses['skipped'][] = $message;
            } else {
                $result = $this->publishMessage($messageToPublish, $entity);
                if (is_int($result)) {
                    $responses['published']++;
                }
                if (is_wp_error($result)) {
                    $responses['errors'][] = $result;
                    $entity->increaseErrorCounter();
                    Logger::logError(sprintf(
                        'Error when publishing post #%s : %s',
                         $result->get_error_code(),
                         $result->get_error_message())
                    );
                    $entityRepository->persist($entity);
                }
            }
        }

        header('HTTP/1.0 200 OK');
        header('Content-Type: application/json');
        echo \json_encode($responses);
        exit();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function home()
    {
        $entityRepository = new EntityRepository();
        $entityList = $entityRepository->findAll([], ['name' => 'desc']);

        $data = [
            'listData' => [
                'listData' => $entityList,
            ],

        ];

        return self::render('home', $data);
    }
}
