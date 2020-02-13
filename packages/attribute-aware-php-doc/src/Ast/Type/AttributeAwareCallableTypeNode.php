<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareCallableTypeNode extends CallableTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    public function __toString(): string
    {
        // keep original (Psalm?) format, see https://github.com/rectorphp/rector/issues/2841
        if ($this->isExplicitCallable()) {
            return $this->createExplicitCallable();
        }

        return 'callable';
    }

    private function isExplicitCallable(): bool
    {
        if (! $this->returnType instanceof IdentifierTypeNode) {
            return false;
        }

        return $this->returnType->name !== 'mixed';
    }

    private function createExplicitCallable(): string
    {
        /** @var IdentifierTypeNode $returnType */
        $returnType = $this->returnType;

        return $this->identifier->name . '():' . $returnType->name;
    }
}
