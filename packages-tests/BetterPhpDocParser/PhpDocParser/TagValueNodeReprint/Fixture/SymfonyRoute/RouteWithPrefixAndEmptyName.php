<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\SymfonyRoute;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/hello", name="route_name")
 */
final class RouteWithPrefixAndEmptyName
{
    /**
     * @Route("/", name="")
     */
    public function run()
    {
    }
}
