<?php

namespace Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\SomeFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SomeController extends Controller
{
    public function action()
    {
        $form = $this->createForm(new SomeFormType());
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\SomeFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SomeController extends Controller
{
    public function action()
    {
        $form = $this->createForm(\Rector\Tests\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\SomeFormType::class);
    }
}

?>
