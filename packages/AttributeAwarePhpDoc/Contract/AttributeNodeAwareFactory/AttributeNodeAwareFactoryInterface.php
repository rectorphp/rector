<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory;

use PHPStan\PhpDocParser\Ast\BaseNode;
use PHPStan\PhpDocParser\Ast\Node;

interface AttributeNodeAwareFactoryInterface
{
    public function isMatch(Node $node): bool;

    /**
     * @return BaseNode
     */
    public function create(Node $node, string $docContent);
}
