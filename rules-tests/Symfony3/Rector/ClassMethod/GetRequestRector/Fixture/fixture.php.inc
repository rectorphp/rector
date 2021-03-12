<?php declare (strict_types=1);

namespace Rector\Tests\Symfony3\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Tests\Symfony3\Rector\Source\SymfonyController;

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

namespace Rector\Tests\Symfony3\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Tests\Symfony3\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function someAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $request->getSomething();
    }
}

?>
