<?php declare (strict_types=1);
class ClassWitNamedService
{
    /**
     * @var SomeClass
     */
    private $someClass;
    public function __construct(SomeClass $someClass)
    {
        $this->someClass = $someClass;
    }
    public function render()
    {
        $this->someClass->render();
    }
}