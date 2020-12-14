<?php declare (strict_types=1);

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Symfony3\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function someAction()
    {
        $this->getRequest()->getSomething();
    }
}

?>
-----
<?php declare (strict_types=1);

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Symfony3\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function someAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $request->getSomething();
    }
}

?>
