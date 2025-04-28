<?php

namespace DatabunkerPro\Tests;

use DatabunkerPro\DatabunkerproApi;
use PHPUnit\Framework\TestCase;

class DatabunkerproAPITest extends TestCase
{
    private $api;
    private static $serverAvailable = false;
    private static $tenantName;
    private static $apiToken;
    private const API_URL = 'https://pro.databunker.org';

    public static function setUpBeforeClass(): void
    {
        // Fetch tenant credentials from DatabunkerPro test environment
        $ch = curl_init('https://databunker.org/api/newtenant.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['status']) && $data['status'] === 'ok') {
                self::$tenantName = $data['tenantname'];
                self::$apiToken = $data['xtoken'];
                // Test connection with new credentials
                $api = new DatabunkerproApi(
                    self::API_URL,
                    self::$apiToken,
                    self::$tenantName
                );
                try {
                    $result = $api->getSystemStats();
                    self::$serverAvailable = is_array($result) && isset($result['status']) && $result['status'] === 'ok';
                } catch (\Exception $e) {
                    self::$serverAvailable = false;
                }
            }
        }
    }

    protected function setUp(): void
    {
        if (!self::$serverAvailable) {
            $this->markTestSkipped('DatabunkerPro server is not available or credentials could not be obtained');
        }

        $this->api = new DatabunkerproApi(
            self::API_URL,
            self::$apiToken,
            self::$tenantName
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