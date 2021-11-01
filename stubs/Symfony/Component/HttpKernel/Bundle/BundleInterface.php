<?php

declare(strict_types=1);

namespace Symfony\Component\HttpKernel\Bundle;

if (interface_exists('Symfony\Component\HttpKernel\Bundle\BundleInterface')) {
    return;
}

interface BundleInterface
{
}
