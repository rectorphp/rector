<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

final class SkipExtraArguments extends \Exception
{
    public function __construct(string $parameter)
    {
        parent::__construct(
            sprintf('Request parameter (%s) is not valid.', $parameter)
        );
    }

    public function run(string $parameter)
    {
        parent::run($parameter . '_');
    }
}
