<?php

declare(strict_types=1);

namespace Symfony\Component\EventDispatcher;

if (interface_exists('Symfony\Contracts\EventDispatcher\EventDispatcherInterface')) {
    return;
}

interface EventDispatcherInterface
{

}
