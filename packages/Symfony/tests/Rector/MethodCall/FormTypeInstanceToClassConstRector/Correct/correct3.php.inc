<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Wrong;

use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\ControllerClass;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormBuilder;

class SomeAnotherControllerWithArgs extends ControllerClass
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', SomeTypeWithCtor::class, ['number' => 1]);
    }
}

class SomeTypeWithCtor
{
    /**
     * @var int
     */
    private $number;
    public function buildForm(\Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormBuilder $builder, array $options)
    {
        $this->number = $options['number'];
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['number' => null]);
    }
}
