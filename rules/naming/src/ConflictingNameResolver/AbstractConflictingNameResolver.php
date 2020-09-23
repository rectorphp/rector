<?php

declare(strict_types=1);

namespace Rector\Naming\ConflictingNameResolver;

use Rector\Naming\ExpectedNameResolver\ExpectedNameResolverInterface;

abstract class AbstractConflictingNameResolver implements ConflictingNameResolverInterface
{
    /**
     * @var ExpectedNameResolverInterface
     */
    protected $expectedNameResolver;

    public function setExpectedNameResolver(ExpectedNameResolverInterface $expectedNameResolver): void
    {
        $this->expectedNameResolver = $expectedNameResolver;
    }
}
