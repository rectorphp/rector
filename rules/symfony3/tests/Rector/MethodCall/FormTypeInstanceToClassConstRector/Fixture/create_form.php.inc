<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\SomeFormType;
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

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\SomeFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SomeController extends Controller
{
    public function action()
    {
        $form = $this->createForm(\Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\SomeFormType::class);
    }
}

?>
