<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Contract;

interface DecoratingNodeFactoryInterface
{
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $baseNode): bool;

    public function create(
        \PHPStan\PhpDocParser\Ast\Node $baseNode,
        string $docContent
    ): \PHPStan\PhpDocParser\Ast\Node;
}
