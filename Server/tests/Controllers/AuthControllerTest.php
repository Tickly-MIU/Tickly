<?php

class AuthControllerTest extends BaseTestCase
{
    private $authController;
    private $mockUserModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for tests
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->mockUserModel = $this->createMock(Users::class);

        $this->authController = new AuthController();

        // Use reflection to inject the mock
        $reflection = new ReflectionClass($this->authController);
        $property = $reflection->getProperty('userModel');
        $property->setAccessible(true);
        $property->setValue($this->authController, $this->mockUserModel);
    }

    protected function tearDown(): void
    {
        // Clean up session
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        parent::tearDown();
    }

    public function testLoginWithValidCredentials()
    {
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'Password123'
        ];

        $userData = [
            'user_id' => 1,
            'email' => 'test@example.com',
            'full_name' => 'Test User',
            'password_hash' => password_hash('Password123', PASSWORD_BCRYPT),
            'role' => 'user'
        ];

        $this->mockUserModel->expects($this->once())
            ->method('getByEmail')
            ->with('test@example.com')
            ->willReturn($userData);

        ob_start();
        $this->authController->login($loginData);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Login successful', $response['message']);
        $this->assertArrayHasKey('user', $response['data']);
        $this->assertEquals(1, $response['data']['user']['id']);
    }

    public function testLoginWithInvalidCredentials()
    {
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $this->mockUserModel->expects($this->once())
            ->method('getByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        ob_start();
        $this->authController->login($loginData);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid email or password', $response['message']);
        $this->assertEquals(401, $response['status_code']);
    }

    public function testRegisterWithValidData()
    {
        $registerData = [
            'full_name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password123'
        ];

        $this->mockUserModel->expects($this->once())
            ->method('exists')
            ->with('new@example.com')
            ->willReturn(false);

        $this->mockUserModel->expects($this->once())
            ->method('register')
            ->willReturn(true);

        ob_start();
        $this->authController->register($registerData);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('User registered successfully', $response['message']);
    }

    public function testRegisterWithExistingEmail()
    {
        $registerData = [
            'full_name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'Password123'
        ];

        $this->mockUserModel->expects($this->once())
            ->method('exists')
            ->with('existing@example.com')
            ->willReturn(true);

        ob_start();
        $this->authController->register($registerData);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Email already registered', $response['message']);
    }
}