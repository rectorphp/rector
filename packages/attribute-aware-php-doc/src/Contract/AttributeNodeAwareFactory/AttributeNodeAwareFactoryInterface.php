<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

interface AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string;

    public function isMatch(Node $node): bool;

    public function create(Node $node, string $docContent): AttributeAwareInterface;
}
