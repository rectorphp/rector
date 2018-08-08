<?php declare (strict_types=1);

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
