<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\SymfonyRoute;

use Symfony\Component\Routing\Annotation\Route;
use Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Source\MyController;

final class RouteNameWithMethodAndClassConstant
{
    /**
     * @Route("/", methods={"GET", "POST"}, name=MyController::ROUTE_NAME)
     */
    public function run()
    {
    }
}

?>
-----
PhpParser\Node\Stmt\ClassMethod
-----
Symfony\Component\Routing\Annotation\Route
