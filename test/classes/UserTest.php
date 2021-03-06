<?php

require __DIR__ . '/../../autoload.php';
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
    
    /**
     * Tests validateDetails() returns true when validating an existing email and password combination.
     */
    public function testSuccessfulValidateDetails() {

        $mockPDO = $this->createMock('PDO');
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->method('fetch')->willReturn([
            'id' => '1',
            'email' => 'example@gmail.com',
            'password' => sha1('1234' . 'aaa'),
            'hash' => '1234'
        ]);
        $mockPDO->method('prepare')->willReturn($mockStatement);

        $user = $this->getMockBuilder('User')
            ->setConstructorArgs(array($mockPDO))
            ->setMethods(array('setUserDetails'))
            ->getMock();

        $user->method('setUserDetails')
            ->willReturn(true);

        $this->assertTrue($user->validateDetails('example@gmail.com','aaa'));

    }

    /**
     * Tests that the expected error will be thrown while trying to validate an email which doesn't exist.
     */
    public function testNonExistantUserValidateDetails() {

        $mockPDO = $this->createMock('PDO');
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->method('fetch')->willReturn([]);
        $mockPDO->method('prepare')->willReturn($mockStatement);

        $user = new User($mockPDO);
        try {
            $user->validateDetails('example@gmail.com','aaa');
        } catch (Exception $e) {
            $this->assertEquals('user does not exist', $e->getMessage());
        }
    }

    /**
     * Tests that the correct error will be thrown while trying to validate using an existing email but an incorrect password.
     */
    public function testIncorrectPasswordValidateDetails() {

        $mockPDO = $this->createMock('PDO');
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->method('fetch')->willReturn([
            'id' => '1',
            'email' => 'example@gmail.com',
            'password' => sha1('1234' . 'aab'),
            'hash' => '1234'
        ]);
        $mockPDO->method('prepare')->willReturn($mockStatement);

        $user = $this->getMockBuilder('User')
            ->setConstructorArgs(array($mockPDO))
            ->setMethods(array('setUserDetails'))
            ->getMock();

        $user->method('setUserDetails')
            ->willReturn(true);

        $errorMessage = '';
        try {
            $user->validateDetails('example@gmail.com','aaa');
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('incorrect email and password combination', $errorMessage);
    }

    /**
     * Tests that validateToken() returns true when given valid details
     */
    public function testSuccessfulValidateToken() {
        $mockPDO = $this->createMock('PDO');
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->method('fetch')->willReturn([
            'id' => 1,
            'email' => 'example@gmail.com',
            'password' => sha1('1234' . 'aab'),
            'hash' => '1234',
            'validationString' => '123'
        ]);
        $mockPDO->method('prepare')->willReturn($mockStatement);

        $user = $this->getMockBuilder('User')
            ->setConstructorArgs(array($mockPDO))
            ->setMethods(array('setUserDetails'))
            ->getMock();

        $user->method('setUserDetails')
            ->willReturn(true);

        $this->assertTrue($user->validateToken('123', 1));
    }

    /**
     * Tests that correct error will be thrown when validateToken() is passed invalid details
     */
    public function testIncorrectTokenValidateToken() {
        $mockPDO = $this->createMock('PDO');
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->method('fetch')->willReturn([
            'id' => 1,
            'email' => 'example@gmail.com',
            'password' => sha1('1234' . 'aab'),
            'hash' => '1234',
            'validationString' => '123'
        ]);
        $mockPDO->method('prepare')->willReturn($mockStatement);

        $user = new User($mockPDO);

        $errorMessage = '';
        try {
            $user->validateToken('124', 1);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('error validating user', $errorMessage);
    }

    /**
     * Tests that setUserDetails() returns true when passed valid array
     */
    public function testSuccessfulSetUserDetails(){
        $mockPDO = $this->createMock('PDO');
        $user = new User($mockPDO);
        $userDetails = ['id'=>'1', 'email'=>'example@email.com', 'hash'=>'123'];

        $this->assertTrue($user->setUserDetails($userDetails));
    }

    //TODO: public function setUserDetails() when passed invalid array

// These weren't very testable functions as they don't output anything
// public function login()
// public function testChangeEmail()
// public function testChangePassword()

// functions impossible to test
// private function validateEmail()
// public function __construct() as sets private variable
}