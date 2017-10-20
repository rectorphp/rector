<?php declare(strict_types=1);

use Symfony\Component\Form\FormEvents;

class SomeClass
{
    public function subscribe()
    {
        return [
            FormEvents::PRE_BIND
        ];
    }
}
