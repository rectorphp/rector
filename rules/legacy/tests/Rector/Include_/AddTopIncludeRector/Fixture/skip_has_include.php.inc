<?php

namespace Rector\Legacy\Tests\Rector\Include_\AddTopIncludeRector\Fixture;

function someFunction() : void
{
    include __DIR__ . '/../autoloader.php';
}
