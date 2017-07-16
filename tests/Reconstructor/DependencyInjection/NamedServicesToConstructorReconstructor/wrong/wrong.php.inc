<?php declare (strict_types=1);

class ClassWitNamedService
{
    public function render()
    {
        $this->get('some.class')->render();
    }
}
