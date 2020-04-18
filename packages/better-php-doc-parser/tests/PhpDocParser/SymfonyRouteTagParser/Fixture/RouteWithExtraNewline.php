<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyRouteTagParser\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class RouteWithExtraNewline
{
    /**
     * @Route(
     *    path="/remove", name="route_name"
     * )
     */
    public function run()
    {
    }
}
