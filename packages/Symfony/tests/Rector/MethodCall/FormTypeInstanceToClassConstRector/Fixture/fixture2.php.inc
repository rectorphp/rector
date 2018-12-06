<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Wrong;

use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\ControllerClass;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormBuilder;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormType;

class SomeAnotherController extends ControllerClass
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', new SomeClass);

        $form = new FormType();
        $form->add('text', new AnotherFormTypeClass);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Wrong;

use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\ControllerClass;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormBuilder;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormType;

class SomeAnotherController extends ControllerClass
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', SomeClass::class);

        $form = new FormType();
        $form->add('text', AnotherFormTypeClass::class);
    }
}

?>
