<?php

namespace SociallymapConnect\tests;

use PHPUnit\Framework\TestCase;
use SociallymapConnect\Configs\Database\TableNameConfig;
use SociallymapConnect\Includes\Enum;
use SociallymapConnect\Includes\Requester;
use SociallymapConnect\Includes\RequesterCurlDriver;
use SociallymapConnect\Includes\RequesterFileGetContentDriver;
use Mocks\RequesterMockDriver;
use SociallymapConnect\Models\MessageRepository;

class SociallymapConnectPluginTest extends TestCase
{
    /**
     * @return Requester
     * @throws \ReflectionException
     * @throws \SociallymapConnect\Includes\Exceptions\Error500Exception
     */
    private function getMockRequester()
    {
        $requester = new Requester();
        $reflection = new \ReflectionClass($requester);
        $property = $reflection->getProperty('driver');
        $property->setAccessible(true);
        $property->setValue($requester, new RequesterMockDriver('dev'));
        return $requester;
    }

    public function testMessageRepository()
    {
        global $wpdb;
        $msgRepo = new MessageRepository();

        $tableName = $wpdb->prefix . TableNameConfig::PUBLISHED;

        $entityId = 1;
        $postId = 1;
        $messageGuid = 1;

        $return = $msgRepo->publishMessage($entityId, $postId, $messageGuid);

        $data = [
            'entity_id' => $entityId,
            'post_id' => $postId,
            'message_guid' => $messageGuid
        ];
        $format = ['%d', '%d', '%s'];

        $this->assertTrue($return);
        $this->assertEquals($wpdb->data[0], $tableName);
        $this->assertEquals($wpdb->data[1], $data);
        $this->assertEquals($wpdb->data[2], $format);
    }

    public function testRequesters()
    {
        $requester = new Requester();
        $this->assertInstanceOf(RequesterCurlDriver::class, $requester->getDriver());

        $reflection = new \ReflectionClass($requester);
        $property = $reflection->getProperty('driver');
        $property->setAccessible(true);
        $property->setValue($requester, new RequesterFileGetContentDriver('dev'));
        $this->assertInstanceOf(RequesterFileGetContentDriver::class, $requester->getDriver());
    }

    public function testRequester()
    {
        $requester = $this->getMockRequester();

        $messagesWithJson = $requester->getMessages('42', 'json', true);
        $messagesWithEmpty = $requester->getMessages('42', 'empty', true);

        $gotAnError = false;
        try {
            $messagesWithError = $requester->getMessages('42', 'error', true);
        }
        catch (\Exception $exception) {
            $gotAnError = true;
        }

        $this->assertCount(2, $messagesWithJson);
        $this->assertEmpty($messagesWithEmpty);
        $this->assertTrue($gotAnError);
    }

    public function testDownloadRequester()
    {
        $requester = $this->getMockRequester();

        $filename = $requester->download('tata');

        $this->assertFileExists($filename);
        unlink($filename);
    }


}
