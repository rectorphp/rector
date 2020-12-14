<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\AnotherFormTypeClass;
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

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\AnotherFormTypeClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;

class SomeAnotherController extends Controller
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', \Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture\SomeClass::class);

        $form = new TextType();
        $form->add('text', \Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\AnotherFormTypeClass::class);
    }
}

?>
