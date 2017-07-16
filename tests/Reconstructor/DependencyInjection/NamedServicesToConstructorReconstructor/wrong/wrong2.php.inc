<?php declare (strict_types=1);

class ClassWitNamedService
{
    public function render()
    {
        $someService = $this->get('some.class');
    }
}
