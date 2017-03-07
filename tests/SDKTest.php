<?php


use PHPUnit\Framework\TestCase;

require_once("mocks/RootsratedMockPosts.php");
require_once(__DIR__ ."/../SDK/RootsratedSDK.php");
require_once(__DIR__ ."/../SDK/RootsratedWebhook.php");

class RootsRatedSDKTest extends TestCase
{
    public function testTokenIsAHexadecimal()
    {
         $this->assertStringMatchesFormat('%x', "2123242342");
    }
    public function testSecretIsAHexadecimal()
    {
         $this->assertStringMatchesFormat('%x', "2123242342");
    }
    public function testKeyIsAHexadecimal()
    {
         $this->assertStringMatchesFormat('%x', "2123242342");
    }
    public function testExecuteHook()
    {

        $posts = new RootsRatedMockPosts();
        $sdk = new RootsRatedSDK();
        $configJson = file_get_contents(__DIR__ .'/config/config_testExecuteHook.json');
        $sdk->setConfig($configJson);
        $webHook = new RootsRatedWebhook();
        $headers = $this->getMockHeaders();
        $body = $this->getMockReqBody();
        $result = $webHook->executeHook($headers, $body, $posts, $sdk);
        $this->assertEquals(true, $result);
    }

    private function getMockHeaders()
    {
        $headers = array();
        $headers['X-Tidal-Event'] = 'distribution_update';
        $headers['X-Tidal-Signature'] = '71ef08f82876a0bd3e5dac28220dde4422a64b225adb653153d92516c0f6d316';
        return $headers;
    }

    private function getMockReqBody()
    {
        $body = file_get_contents(__DIR__ .'/mocks/hook_body.json');
        return $body;
    }

}
