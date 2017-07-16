<?php declare (strict_types=1);
class ClassWitNamedService
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
        $this->stdClass->render();
    }
}