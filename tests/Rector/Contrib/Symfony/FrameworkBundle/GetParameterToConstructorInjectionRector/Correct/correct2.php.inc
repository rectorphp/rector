<?php declare (strict_types=1);

namespace SomeNamespace;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassWithNamedService3 extends Controller
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
