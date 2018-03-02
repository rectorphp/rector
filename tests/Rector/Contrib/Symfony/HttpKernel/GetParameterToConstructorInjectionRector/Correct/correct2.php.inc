<?php declare (strict_types=1);

namespace SomeNamespace;

class ClassWithNamedService2 implements ContainerAwareInterface
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
    }
}
