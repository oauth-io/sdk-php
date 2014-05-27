<?php

namespace OAuth_ioTest;

use OAuth_io\OAuth;

class InitialTest extends \PHPUnit_Framework_TestCase
{
    protected $oauth;

    protected function setUp()
    {
        $this->oauth = new OAuth();
    }

    public function testInitializeSetsKeyAndSecret()
    {
        if (method_exists($this->oauth, 'initialize') && method_exists($this->oauth, 'getAppKey') && method_exists($this->oauth, 'getAppSecret')) {
            $this->oauth->initialize('somekey', 'somesecret');

            $this->assertEquals($this->oauth->getAppKey(), 'somekey');
            $this->assertEquals($this->oauth->getAppSecret(), 'somesecret');
        } else {
            $this->fail('OAuth::initialize() does not exist');
        }
    }

    public function testOauthdUrlIsOauthioByDefault()
    {
        if (method_exists($this->oauth, 'initialize') && method_exists($this->oauth, 'setOAuthdUrl')) {

            $this->assertEquals('https://oauth.io', $this->oauth->getOAuthdUrl());

        } else {
            $this->fail('methods are missing');
        }
    }

    public function testSetOauthdUrlSetsUrlInObject()
    {
        if (method_exists($this->oauth, 'initialize') && method_exists($this->oauth, 'setOAuthdUrl')) {
            $this->oauth->initialize('somekey', 'somesecret');
            $this->oauth->setOAuthdUrl('https://oauthd.local');

            $this->assertEquals($this->oauth->getOAuthdUrl(), 'https://oauthd.local');

        } else {
            $this->fail('methods are missing');
        }
    }

    /** 
     * @expectedException OAuth_io\Exception\NotInitializedException
     */
    public function testCallingAuthWhenNotInitializedThrowsAnException()
    {
        $this->oauth->auth('somecode');
    }

    /** 
     * @expectedException OAuth_io\Exception\NotInitializedException
     */
    public function testCallingCreateWhenNotInitializedThrowsAnException()
    {
        $this->oauth->create('somecode');
    }
}
