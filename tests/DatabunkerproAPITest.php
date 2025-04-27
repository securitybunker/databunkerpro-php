<?php

namespace DatabunkerPro\Tests;

use DatabunkerPro\DatabunkerproAPI;
use PHPUnit\Framework\TestCase;

class DatabunkerproAPITest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new DatabunkerproAPI(
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