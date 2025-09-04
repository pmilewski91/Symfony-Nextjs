<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthControllerTest extends WebTestCase
{
    public function testHealthCheck(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('timestamp', $data);
        
        $this->assertEquals('OK', $data['status']);
        $this->assertEquals('Conference Room Booking API is running', $data['message']);
        $this->assertNotEmpty($data['timestamp']);
    }

    public function testApiHealthCheck(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/health');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('api', $data);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('timestamp', $data);
        
        $this->assertEquals('Conference Room Booking', $data['api']);
        $this->assertEquals('1.0.0', $data['version']);
        $this->assertEquals('healthy', $data['status']);
        $this->assertNotEmpty($data['timestamp']);
    }
}
