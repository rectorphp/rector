<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class SkipSymfonyRoute
{
    /**
     * @Route(
     *     "/{arg1}/{arg2}",
     *     defaults={"arg1"=null, "arg2"=""},
     *     requirements={"arg1"="\d+", "arg2"=".*"}
     * )
     */
    public function nothing(): void
    {
    }
}
