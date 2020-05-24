<?php

declare(strict_types=1);

namespace Kdyby\Events;

if (interface_exists('Kdyby\Events\Subscriber')) {
    return;
}

interface Subscriber
{

}
