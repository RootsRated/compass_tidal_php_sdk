<?php


use PHPUnit\Framework\TestCase;

require_once("mocks/RootsratedMockPosts.php");
require_once(__DIR__ ."/../SDK/RootsratedSDK.php");
require_once(__DIR__ ."/../SDK/RootsratedWebhook.php");
require_once(__DIR__ ."/../SDK/RootsratedError.php");

class RootsRatedSDKTest extends TestCase
{
    public function testExecuteHook()
    {
        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $event = 'distribution_update';
        $path = '/mocks/hook_body.json';
        $body = $this->getMockReqBody($path);
        $headers = $this->getMockHeaders($event, $body, $configJson);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }
    public function testCancel()
    {
        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $event = 'service_cancel';
        $path = '/mocks/hook_cancel.json';
        $body = $this->getMockReqBody($path);
        $headers = $this->getMockHeaders($event, $body, $configJson);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }
    public function testContentUpdate()
    {
        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $event = 'content_update';
        $path = '/mocks/hook_content_update.json';
        $body = $this->getMockReqBody($path);
        $headers = $this->getMockHeaders($event, $body, $configJson);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }
    public function testDistributionUpdate()
    {
        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $event = 'distribution_update';
        $path = '/mocks/hook_distribution_update.json';
        $body = $this->getMockReqBody($path);
        $headers = $this->getMockHeaders($event, $body, $configJson);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }
    public function testRevoke()
    {
        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $event = 'distribution_revoke';
        $path = '/mocks/hook_revoke.json';
        $body = $this->getMockReqBody($path);
        $headers = $this->getMockHeaders($event, $body, $configJson);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }
    public function testScheduling()
    {
        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $event = 'distribution_schedule';
        $path = '/mocks/hook_scheduling.json';
        $body = $this->getMockReqBody($path);
        $headers = $this->getMockHeaders($event, $body, $configJson);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }

    public function testPhoneHome()
    {
        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $event = 'service_phone_home';
        $headers = $this->getMockHeaders($event);
        $path = '/mocks/hook_phone_home.json';
        $body = $this->getMockReqBody($path);
        $headers = $this->getMockHeaders($event, $body, $configJson);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertRegexp('/system_info/', $result);
    }

    private function getMockHeaders($event, $body, $configJson)
    {
        $configJsonArray = json_decode($configJson, true);

        $headers = array();
        $headers['X-Tidal-Event'] = $event;
        $headers['X-Tidal-Signature'] = hash_hmac('sha256', $body, $configJsonArray['rootsrated']['rootsrated_secret']);
        return $headers;
    }

    private function getMockReqBody($path)
    {
        $body = file_get_contents(__DIR__ .$path);
        return json_encode(json_decode($body));
    }

}
