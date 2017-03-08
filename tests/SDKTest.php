<?php


use PHPUnit\Framework\TestCase;

require_once("mocks/RootsratedMockPosts.php");
require_once(__DIR__ ."/../SDK/RootsratedSDK.php");
require_once(__DIR__ ."/../SDK/RootsratedWebhook.php");

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
        $headers = $this->getMockHeaders($event);
        $path = '/mocks/hook_body.json';
        $body = $this->getMockReqBody($path);
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
        $headers = $this->getMockHeaders($event);
        $path = '/mocks/hook_cancel.json';
        $body = $this->getMockReqBody($path);
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
        $headers = $this->getMockHeaders($event);
        $path = '/mocks/hook_content_update.json';
        $body = $this->getMockReqBody($path);
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
        $headers = $this->getMockHeaders($event);
        $path = '/mocks/hook_distribution_update.json';
        $body = $this->getMockReqBody($path);
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
        $headers = $this->getMockHeaders($event);
        $path = '/mocks/hook_revoke.json';
        $body = $this->getMockReqBody($path);
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
        $headers = $this->getMockHeaders($event);
        $path = '/mocks/hook_scheduling.json';
        $body = $this->getMockReqBody($path);
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }

    private function getMockHeaders($event)
    {
        $headers = array();
        $headers['X-Tidal-Event'] = $event;
        $headers['X-Tidal-Signature'] = '71ef08f82876a0bd3e5dac28220dde4422a64b225adb653153d92516c0f6d316';
        return $headers;
    }

    private function getMockReqBody($path)
    {
        $body = file_get_contents(__DIR__ .$path);
        return $body;
    }

}
