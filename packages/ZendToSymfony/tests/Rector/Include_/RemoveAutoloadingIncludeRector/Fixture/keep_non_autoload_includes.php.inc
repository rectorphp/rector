<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Include_\RemoveAutoloadingIncludeRector\Fixture;

final class KeepNonAutoloadIncludes
{
    public function run()
    {
        include_once 'data.txt';

        // probably needed for some other operation
        return require 'SomeFile.php';
    }
}
