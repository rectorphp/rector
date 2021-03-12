<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\StringFormTypeToClassRector\Fixture;

use Symfony\Component\Form\FormBuilder;

final class SomePresenter
{
    public function action()
    {
        $formBuilder = new FormBuilder();
        $formBuilder->add('task', 'form.type.text');
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\StringFormTypeToClassRector\Fixture;

use Symfony\Component\Form\FormBuilder;

final class SomePresenter
{
    public function action()
    {
        $formBuilder = new FormBuilder();
        $formBuilder->add('task', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
    }
}

?>
