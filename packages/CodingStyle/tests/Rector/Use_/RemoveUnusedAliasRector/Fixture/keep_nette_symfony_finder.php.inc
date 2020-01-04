<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Nette\Utils\Finder as NetteFinder;
use Symfony\Component\Finder\Finder as SymfonyFinder;

final class KeepNetteSymfonyFinder
{
    public function nette()
    {
        return new NetteFinder();
    }

    public function symfony()
    {
        return new SymfonyFinder();
    }
}
