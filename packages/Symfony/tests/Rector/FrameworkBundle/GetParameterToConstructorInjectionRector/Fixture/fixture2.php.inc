<?php

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector\Source\DoctrineQuery;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService3 extends SymfonyController
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

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector\Source\DoctrineQuery;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService3 extends SymfonyController
{
    /**
     * @var string
     */
    private $someParameter;
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
