<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\SymfonyRoute;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @see https://symfony.com/doc/master/bundles/FOSJsRoutingBundle/usage.html
 */
final class RouteWithExposeOption
{
    /**
     * @Route("/foo/{id}/bar", options={"expose"=true}, name="my_route_to_expose")
     */
    public function run()
    {
    }
}
