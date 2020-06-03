<?php

declare(strict_types=1);

namespace Symfony\Component\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (interface_exists('Symfony\Component\Routing\RouterInterface')) {
    return;
}

interface RouterInterface extends UrlGeneratorInterface
{

}
