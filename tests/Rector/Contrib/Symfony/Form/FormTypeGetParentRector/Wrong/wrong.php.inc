<?php declare (strict_types=1);

use Symfony\Component\Form\AbstractType;

class PermissionCollectionType extends AbstractType
{
    public function getName()
    {
        return 'some_name';
    }

    public function getParent()
    {
        return 'collection';
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}