<?php declare (strict_types=1);

class ClassWithNamedService extends Controller
{
    public function render()
    {
        $this->get('some.class')->render();
    }
}
