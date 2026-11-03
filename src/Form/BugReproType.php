<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BugReproType extends AbstractType
{
    public const VALIDATION_GROUP = 'with_number';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(groups: [self::VALIDATION_GROUP]),
                ],
            ])
            ->add('text', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(groups: [self::VALIDATION_GROUP]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Validation should only run when the number field contains a value.
            // Returning an empty array makes the form validator skip validation.
            'validation_groups' => static function (FormInterface $form): array {
                return null === $form->get('number')->getData()
                    ? []
                    : [self::VALIDATION_GROUP];
            },
        ]);
    }
}
