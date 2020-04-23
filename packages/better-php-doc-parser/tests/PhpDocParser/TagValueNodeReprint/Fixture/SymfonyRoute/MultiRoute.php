<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\SymfonyRoute;

use Symfony\Component\Routing\Annotation\Route;

final class MultiRoute
{
    /**
     * @Route("/new", name="route_1", methods={"GET", "POST"})
     * @Route("/{id}", name="route_2", methods={"GET", "POST"})
     */
    public function run()
    {
    }
}
