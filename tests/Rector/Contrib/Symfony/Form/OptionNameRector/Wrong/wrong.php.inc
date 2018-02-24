<?php

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'form.name'))
            ->add('price1', 'text', array(
                'label' => 'form.price1',
                'precision' => 3,
            ))
            ->add('price2', 'text', array(
                'precision' => 3,
            ))
            ->add('discount', 'integer', [
                'label' => 'form.email',
                'virtual' => true,
            ])
            ->add('password', 'password')
        ;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}