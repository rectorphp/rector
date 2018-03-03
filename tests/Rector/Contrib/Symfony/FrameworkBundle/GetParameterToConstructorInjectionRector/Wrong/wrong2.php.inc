<?php declare (strict_types=1);

namespace SomeNamespace;

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
