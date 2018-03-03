<?php declare (strict_types=1);

namespace SomeNamespace;

class ClassWithNamedService3 implements ContainerAwareInterface
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
        $this->renderTwig([
            'value' => $this->someParameter
        ]);

        $query = new DoctrineQuery();
        $queryId = $query->getParameter('id');
    }
}
