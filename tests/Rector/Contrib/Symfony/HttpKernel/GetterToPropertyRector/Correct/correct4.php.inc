<?php declare (strict_types=1);

namespace SomeNamespace;

class ClassWithNamedService4 implements ContainerAwareInterface
{
    /**
     * @var stdClass
     */
    private $stdClass;
    public function __construct(stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->stdClass->callMe()
        ]);
    }
}
