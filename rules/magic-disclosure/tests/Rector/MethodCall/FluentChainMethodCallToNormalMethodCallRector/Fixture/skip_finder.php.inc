<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Symfony\Component\Finder\Finder;

class SkipFinder
{
    public function someFunction()
    {
        $finder = (new Finder())
            ->files()
            ->in(__DIR__)
            ->getIterator();
    }
}
