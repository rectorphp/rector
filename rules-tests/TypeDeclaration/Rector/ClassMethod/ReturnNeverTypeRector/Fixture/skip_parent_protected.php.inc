<?php

use Rector\Core\Exception\ShouldNotHappenException;

final class SkipParentProtected implements SomeInterfaceWithReturnType
{
    public function run()
    {
        throw new ShouldNotHappenException();
    }
}

interface SomeInterfaceWithReturnType
{
    public function run(): void;
}
