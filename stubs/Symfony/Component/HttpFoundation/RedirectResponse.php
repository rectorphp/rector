<?php

declare(strict_types=1);

namespace Symfony\Component\HttpFoundation;

if (class_exists('Symfony\Component\HttpFoundation\RedirectResponse')) {
    return;
}

final class RedirectResponse extends Response
{

}
