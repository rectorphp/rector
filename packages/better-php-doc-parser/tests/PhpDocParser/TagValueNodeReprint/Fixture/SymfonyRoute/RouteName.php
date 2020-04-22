<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\SymfonyRoute;

use Symfony\Component\Routing\Annotation\Route;

final class RouteName
{
    /**
     * @Route("/hello/", name=TestController::ROUTE_NAME)
     */
    public function run()
    {
    }
}
