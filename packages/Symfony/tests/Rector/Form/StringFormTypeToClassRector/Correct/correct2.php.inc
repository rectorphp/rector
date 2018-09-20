<?php

declare(strict_types=1);

namespace Sylius\Bundle\CoreBundle\Form\Type;

use Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Source\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

final class ContactType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'sylius.ui.email',
            ])
        ;
    }
}
