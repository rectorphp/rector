<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
{
    public function render()
    {
        $value = $this->getParameter('some_parameter');
    }
}
