<?php declare(strict_types=1);

namespace SomeNamespace;

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
