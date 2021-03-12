<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

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
