<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RenameParamDefaultInit\Fixture;

class EmptyIfStmts
{
    public function run()
    {
        if (rand(0,1)) {}
    }
}
