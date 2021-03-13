<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory\Type;

use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Rector\AttributeAwarePhpDoc\ValueObject\Type\BetterCallableTypeNode;

final class BetterCallableTypeNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $baseNode): bool
    {
        return $baseNode instanceof CallableTypeNode;
    }

    /**
     * @param CallableTypeNode $baseNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $baseNode, string $docContent): \PHPStan\PhpDocParser\Ast\Node
    {
        return new BetterCallableTypeNode($baseNode->identifier, $baseNode->parameters, $baseNode->returnType, );
    }
}
