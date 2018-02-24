<?php declare (strict_types=1);

use Symfony\Component\Form\AbstractTypeExtension;

class TypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return \Symfony\Component\Form\Extension\Core\Type\FormType::class;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}