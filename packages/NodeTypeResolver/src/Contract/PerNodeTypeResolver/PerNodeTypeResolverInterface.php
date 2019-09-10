<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\PerNodeTypeResolver;

use PhpParser\Node;
use PHPStan\Type\Type;

interface PerNodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array;

    public function resolve(Node $node): Type;
}
