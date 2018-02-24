<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
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
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}