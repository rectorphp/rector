<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyRouteTagParser\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SomeClassMethod
{
    /**
     * @Route({"en":"/en"}, name="homepage")
     */
    public function run()
    {
    }
}
