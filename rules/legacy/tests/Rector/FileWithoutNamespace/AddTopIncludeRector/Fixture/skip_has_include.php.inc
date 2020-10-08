<?php

namespace Rector\Legacy\Tests\Rector\FileWithoutNamespace\AddTopIncludeRector\Fixture;

function someFunction() : void
{
    include __DIR__ . '/../autoloader.php';
}
