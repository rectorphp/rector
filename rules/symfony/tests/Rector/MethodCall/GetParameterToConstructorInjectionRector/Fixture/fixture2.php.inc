<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\Source\DoctrineQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassWithNamedService3 extends Controller
{
    public function render()
    {
        $value = $this->getParameter('some_parameter');

        $query = new DoctrineQuery();
        $queryId = $query->getParameter('id');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\Source\DoctrineQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassWithNamedService3 extends Controller
{
    private string $someParameter;
    public function __construct(string $someParameter)
    {
        $this->someParameter = $someParameter;
    }
    public function render()
    {
        $value = $this->someParameter;

        $query = new DoctrineQuery();
        $queryId = $query->getParameter('id');
    }
}

?>
