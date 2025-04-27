<?php

namespace DatabunkerPro\Tests;

use DatabunkerPro\DatabunkerproApi;
use PHPUnit\Framework\TestCase;

class DatabunkerproAPITest extends TestCase
{
    private $api;
    private static $serverAvailable = false;

    public static function setUpBeforeClass()
    {
        $api = new DatabunkerproApi(
            getenv('DATABUNKER_URL'),
            getenv('DATABUNKER_TOKEN'),
            getenv('DATABUNKER_TENANT')
        );

        try {
            $result = $api->getSystemStats();
            self::$serverAvailable = is_array($result) && isset($result['status']) && $result['status'] === 'ok';
        } catch (\Exception $e) {
            self::$serverAvailable = false;
        }
    }

    protected function setUp()
    {
        if (!self::$serverAvailable) {
            $this->markTestSkipped('DatabunkerPro server is not available');
        }

        $this->api = new DatabunkerproApi(
            getenv('DATABUNKER_URL'),
            getenv('DATABUNKER_TOKEN'),
            getenv('DATABUNKER_TENANT')
        );
    }

    public function testCreateUser()
    {
        $result = $this->api->createUser([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    public function testGetUser()
    {
        $result = $this->api->getUser('email', 'test@example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    public function testUpdateUser()
    {
        $result = $this->api->updateUser('email', 'test@example.com', [
            'name' => 'Updated User'
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }
} 