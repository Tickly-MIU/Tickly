<?php

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Common setup for all tests
        // You can initialize database connections, mock objects, etc. here
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Common cleanup for all tests
    }

    // Helper methods that can be used across all test classes
    protected function createMockResponse($data = null, $statusCode = 200)
    {
        $mock = $this->createMock(Response::class);
        $mock->method('send')->willReturn(json_encode($data));
        $mock->method('getStatusCode')->willReturn($statusCode);
        return $mock;
    }

    protected function createMockRequest($method = 'GET', $uri = '/', $params = [])
    {
        // Since there's no Request class, return an array or simple mock
        return [
            'method' => $method,
            'uri' => $uri,
            'params' => $params
        ];
    }
}