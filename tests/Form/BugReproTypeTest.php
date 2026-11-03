<?php

namespace App\Tests\Form;

use App\Form\BugReproType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;

class BugReproTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    #[DataProvider('emptyNumberValues')]
    public function testValidationIsSkippedWhenNumberIsNull(?string $number): void
    {
        $form = $this->formFactory->create(BugReproType::class);
        $form->submit([
            'number' => $number,
            'text' => '',
        ]);

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid(), $this->formatErrors($form));
        self::assertCount(0, $form->getErrors(true));
    }

    #[DataProvider('nonNullNumberValues')]
    public function testValidationRunsWhenNumberIsNotNull(int $number): void
    {
        $form = $this->formFactory->create(BugReproType::class);
        $form->submit([
            'number' => $number,
            'text' => '',
        ]);

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());

        $errors = iterator_to_array($form->getErrors(true));
        self::assertCount(1, $errors);
        self::assertSame('This value should not be blank.', $errors[0]->getMessage());
        self::assertSame('text', $errors[0]->getOrigin()->getName());
    }

    public function testInvalidNumberReturnsTransformationFailureError(): void
    {
        $form = $this->formFactory->create(BugReproType::class);
        $form->submit([
            'number' => 'invalid',
            'text' => '',
        ]);


        self::assertTrue($form->isSubmitted());

        $number = $form->get('number');
        self::assertFalse($number->isSynchronized());
        self::assertInstanceOf(TransformationFailedException::class, $number->getTransformationFailure());

        self::assertFalse(
            $form->isValid(),
            'The form should be invalid when the number value cannot be transformed. Actual errors: '.$this->formatErrors($form),
        );

        $errors = iterator_to_array($form->getErrors(true));
        self::assertCount(1, $errors);
        self::assertSame('This value is not valid.', $errors[0]->getMessage());
        self::assertSame('number', $errors[0]->getOrigin()->getName());
    }

    public function testNoErrorsWhenNumberAndTextAreValid(): void
    {
        $form = $this->formFactory->create(BugReproType::class);
        $form->submit([
            'number' => 42,
            'text' => 'some text',
        ]);

        self::assertTrue($form->isValid(), $this->formatErrors($form));
        self::assertCount(0, $form->getErrors(true));
    }

    public static function emptyNumberValues(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
    }

    public static function nonNullNumberValues(): iterable
    {
        yield 'zero' => [0];
        yield 'positive number' => [42];
    }

    private function formatErrors(\Symfony\Component\Form\FormInterface $form): string
    {
        $messages = array_map(
            static fn (FormError $error): string => $error->getMessage(),
            iterator_to_array($form->getErrors(true)),
        );

        return implode('; ', $messages);
    }
}
