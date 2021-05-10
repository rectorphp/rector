<?php

declare(strict_types=1);

namespace Rector\Privatization\Naming;

use PhpParser\Node\Stmt\PropertyProperty;
use Rector\NodeNameResolver\NodeNameResolver;
use Stringy\Stringy;

final class ConstantNaming
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function createFromProperty(PropertyProperty $propertyProperty): string
    {
        $propertyName = $this->nodeNameResolver->getName($propertyProperty);

        $stringy = new Stringy($propertyName);
        return (string) $stringy->underscored()
            ->toUpperCase();
    }
}
