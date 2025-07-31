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
                    if (self::$serverAvailable) {
                        echo "\nSuccessfully connected to DatabunkerPro server\n";
                        echo "Tenant: " . self::$tenantName . "\n";
                        echo "API URL: " . self::API_URL . "\n";
                    }
                } catch (\Exception $e) {
                    self::$serverAvailable = false;
                    echo "\nFailed to connect to DatabunkerPro server: " . $e->getMessage() . "\n";
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
        echo "\nTesting user creation...\n";
        $profile = [
            'email' => 'test' . rand(1000, 999999) . '@example.com',
            'name' => 'Test User ' . rand(1000, 999999),
            'phone' => rand(1000, 999999)
        ];
        $result = $this->api->createUser($profile);
        $this->assertInternalType('array', $result, 'Response should be an array');
        $this->assertArrayHasKey('status', $result, 'Response should have status key');
        $this->assertEquals('ok', $result['status'], 'Status should be ok');
        $this->assertArrayHasKey('token', $result, 'Response should have token key');
        echo "Created user with email: " . $profile['email'] . "\n";
        return $profile['email'];
    }

    public function testGetUser()
    {
        echo "\nTesting user retrieval...\n";
        $email = $this->testCreateUser();
        $result = $this->api->getUser('email', $email);
        $this->assertInternalType('array', $result, 'Response should be an array');
        $this->assertArrayHasKey('status', $result, 'Response should have status key');
        $this->assertEquals('ok', $result['status'], 'Status should be ok');
        $this->assertArrayHasKey('profile', $result, 'Response should have profile key');
        echo "Retrieved user with email: " . $email . "\n";
        return $email;
    }

    public function testUpdateUser()
    {
        echo "\nTesting user update...\n";
        $email = $this->testGetUser();
        $profile = [
            'name' => 'Updated Test User',
            'phone' => '+9876543210'
        ];
        $result = $this->api->updateUser('email', $email, $profile);
        $this->assertInternalType('array', $result, 'Response should be an array');
        $this->assertArrayHasKey('status', $result, 'Response should have status key');
        $this->assertEquals('ok', $result['status'], 'Status should be ok');
        // Verify the update
        $updatedUser = $this->api->getUser('email', $email);
        $this->assertEquals($profile['name'], $updatedUser['profile']['name'], 'Name should be updated');
        $this->assertEquals($profile['phone'], $updatedUser['profile']['phone'], 'Phone should be updated');
        echo "Updated user with email: " . $email . "\n";
        echo "New name: " . $profile['name'] . "\n";
        echo "New phone: " . $profile['phone'] . "\n";
    }
} 