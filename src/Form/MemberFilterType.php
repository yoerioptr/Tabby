<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\MemberFilter;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MemberFilterType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => false,
        ]);
        $builder->add('ranking', ChoiceType::class, [
            'choices' => $options['rankings'],
            'choice_label' => static fn (string $ranking): string => $ranking,
            'placeholder' => 'All rankings',
            'required' => false,
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberFilter::class,
            'method' => 'GET',
            'csrf_protection' => false, // Required for get method
            'allow_extra_fields' => true,
            'required' => false,
            'rankings' => [],
        ]);

        $resolver->setAllowedTypes('rankings', 'array');
    }
}
