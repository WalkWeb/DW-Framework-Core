<?php

declare(strict_types=1);

namespace Tests\src\WalkWeb\NWFramework;

use Exception;
use WalkWeb\NW\AppException;
use Tests\AbstractTest;

class ValidatorTest extends AbstractTest
{
    /**
     * @throws AppException
     */
    public function testValidatorEmail(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('email', 'mail@mail.com', ['mail']));

        // False
        self::assertFalse($validator->check('email', 'mail@mail', ['mail']));
        self::assertEquals('Указана некорректная почта', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorInteger(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('integer', 100, ['int']));

        // False
        self::assertFalse($validator->check('integer', '100', ['int']));
        self::assertEquals('integer должен быть числом', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorString(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('string', 'name', ['string']));

        // False
        self::assertFalse($validator->check('string', 100, ['string']));
        self::assertEquals('string должен быть строкой', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorBoolean(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('boolean', false, ['boolean']));

        // False
        self::assertFalse($validator->check('boolean', 1, ['boolean']));
        self::assertEquals('boolean должен быть логическим типом', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorIn(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('in', 10, ['in' => [10, 20, 30]]));

        // False
        self::assertFalse($validator->check('in', 100, ['in' => [10, 20, 30]]));
        self::assertEquals('in указан некорректно', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorParent(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('parent', 'Login', ['parent' => '/^[a-zA-Z0-9а-яА-ЯёЁ\-_]*$/u',]));

        // False
        self::assertFalse($validator->check('parent', 'InvalidLogin&', ['parent' => '/^[a-zA-Z0-9а-яА-ЯёЁ\-_]*$/u',]));
        self::assertEquals('parent указан некорректно', $validator->getError());
    }

    /**
     * Проверка минимальной длины строки
     *
     * @throws AppException
     */
    public function testValidatorMinString(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('min string', 'Login', ['string', 'min' => 5]));

        // False
        self::assertFalse($validator->check('min string', 'Login', ['string', 'min' => 10]));
        self::assertEquals('min string должен быть больше или равен 10 символов', $validator->getError());
    }

    /**
     * Проверка минимального значения int
     *
     * @throws AppException
     */
    public function testValidatorMinInt(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('min int', 10, ['int', 'min' => 9]));

        // False
        self::assertFalse($validator->check('min int', 9, ['int', 'min' => 10]));
        self::assertEquals('min int должен быть больше или равен 10', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorMaxString(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('max string', 'Login', ['string', 'max' => 10]));

        // False
        self::assertFalse($validator->check('max string', 'Login', ['string', 'max' => 3]));
        self::assertEquals('max string должен быть меньше или равен 3 символов', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorMaxInt(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('max int', 10, ['int', 'max' => 10]));

        // False
        self::assertFalse($validator->check('max int', 4, ['int', 'max' => 3]));
        self::assertEquals('max int должен быть меньше или равен 3', $validator->getError());
    }

    /**
     * @throws AppException
     */
    public function testValidatorRequired(): void
    {
        $validator = $this->getContainer()->getValidator();

        // Success
        self::assertTrue($validator->check('required', 123, ['required']));

        // False
        self::assertFalse($validator->check('required', null, ['required']));
        self::assertEquals('required не может быть пустым', $validator->getError());

        // No required
        self::assertTrue($validator->check('no required', null, []));
    }

    /**
     * @throws AppException
     */
    public function testValidatorCustomError(): void
    {
        $validator = $this->getContainer()->getValidator();

        $error = 'Вы указали некорректный логи';
        $rules = [
            'required',
            'string',
            'min'    => 5,
            'max'    => 15,
            'parent' => '/^[a-zA-Z0-9а-яА-ЯёЁ\-_]*$/u',
            'unique',
        ];

        self::assertFalse($validator->check('login', 'InvalidLogin&', $rules, null, null, $error));
        self::assertEquals($error, $validator->getError());
    }
}
