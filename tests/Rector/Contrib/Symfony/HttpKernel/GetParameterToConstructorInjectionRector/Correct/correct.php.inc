<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
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
    }
}
