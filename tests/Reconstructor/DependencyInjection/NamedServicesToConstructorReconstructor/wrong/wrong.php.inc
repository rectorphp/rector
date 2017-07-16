<?php declare (strict_types=1);

class ClassWitNamedService extends Controller
{
    public function render()
    {
        $this->get('some.class')->render();
    }
}
