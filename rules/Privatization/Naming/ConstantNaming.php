<?php

declare(strict_types=1);

namespace Rector\Privatization\Naming;

use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeNameResolver\NodeNameResolver;
use Stringy\Stringy;

final class ConstantNaming
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function createFromProperty(PropertyProperty $propertyProperty): string
    {
        $propertyName = $this->nodeNameResolver->getName($propertyProperty);

        $stringy = new Stringy($propertyName);
        return (string) $stringy->underscored()->toUpperCase();
    }
}
