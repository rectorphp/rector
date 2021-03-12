<?php

namespace Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\AnotherFormTypeClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;

class SomeAnotherController extends Controller
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', new SomeClass);

        $form = new TextType();
        $form->add('text', new AnotherFormTypeClass());
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\AnotherFormTypeClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;

class SomeAnotherController extends Controller
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', \Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture\SomeClass::class);

        $form = new TextType();
        $form->add('text', \Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\AnotherFormTypeClass::class);
    }
}

?>
