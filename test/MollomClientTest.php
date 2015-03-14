<?php

require_once __DIR__ . '/../vendor/autoload.php';

use sbscomp\mollom\MollomClient;

class MollomClientTest extends \PHPUnit_Framework_TestCase {
    private $client;
    private $sitePK;

    protected function setUp()
    {
        $this->client = new MollomClient('abcd','abcd');
        $this->client->enableDevelopmentMode(true);
        $this->assertEquals("dev.mollom.com", $this->client->server);
        $this->client->setPlatformName("MollomClientUnitTest");
        $this->client->setPlatformVersion("1.0.0");

        $createResult = $this->client->createSite(array(
            'url' => 'http://www.nowhere.bbb',
            'email' => 'testina@rabbit.bbb'
        ));
        $this->assertTrue(is_array($createResult));
        $this->client->setCredentials($createResult['publicKey'], $createResult['privateKey']);
        $this->sitePK = $createResult['publicKey'];
        $this->client->enableDevelopmentMode(false);
    }

    protected function tearDown()
    {
        $this->client->deleteSite($this->sitePK);
    }

    public function testMollomSpam()
    {
        $result = $this->client->checkContent(array(
            'checks' => array('spam'),
            'postBody' => 'spam',
            'authorName' => 'Test Rabbit',
            'authorUrl' => 'http://www.nowhere.zzz',
            'authorIp' => '127.0.0.1'
        ));
        $this->assertTrue(is_array($result), "Expected result array, got " . print_r($result,true));
        $this->assertNotNull($result['id']);
        $this->assertEquals("spam", $result['spamClassification']);
    }

    public function testMollomHam()
    {
        $result = $this->client->checkContent(array(
            'checks' => array('spam'),
            'postBody' => 'ham',
            'authorName' => 'Test Rabbit',
            'authorUrl' => 'http://www.nowhere.zzz',
            'authorIp' => '127.0.0.1'
        ));
        $this->assertTrue(is_array($result), "Expected result array, got " . print_r($result,true));
        $this->assertNotNull($result['id']);
        $this->assertEquals("ham", $result['spamClassification']);
    }

    public function testMollomUnsure()
    {
        $result = $this->client->checkContent(array(
            'checks' => array('spam'),
            'postBody' => 'unsure',
            'authorName' => 'Test Rabbit',
            'authorUrl' => 'http://www.nowhere.zzz',
            'authorIp' => '127.0.0.1'
        ));
        $this->assertTrue(is_array($result), "Expected result array, got " . print_r($result,true));
        $this->assertNotNull($result['id']);
        $this->assertEquals("unsure", $result['spamClassification']);
    }
}
