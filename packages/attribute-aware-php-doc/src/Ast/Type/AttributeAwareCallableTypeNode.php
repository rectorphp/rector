<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareCallableTypeNode extends CallableTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    public function __toString(): string
    {
        // keep original (Psalm?) format, see https://github.com/rectorphp/rector/issues/2841
        return $this->createExplicitCallable();
    }

    private function createExplicitCallable(): string
    {
        /** @var IdentifierTypeNode|GenericTypeNode $returnType */
        $returnType = $this->returnType;

        $parameterTypeString = $this->createParameterTypeString();

        $returnTypeAsString = (string) $returnType;
        if (Strings::contains($returnTypeAsString, '|')) {
            $returnTypeAsString = '(' . $returnTypeAsString . ')';
        }

        $parameterTypeString = $this->normalizeParameterType($parameterTypeString, $returnTypeAsString);
        $returnTypeAsString = $this->normalizeReturnType($parameterTypeString, $returnTypeAsString);

        return sprintf('%s%s%s', $this->identifier->name, $parameterTypeString, $returnTypeAsString);
    }

    private function createParameterTypeString(): string
    {
        $parameterTypeStrings = [];
        foreach ($this->parameters as $parameter) {
            $parameterTypeStrings[] = trim((string) $parameter);
        }

        $parameterTypeString = implode(', ', $parameterTypeStrings);

        return trim($parameterTypeString);
    }

    private function normalizeParameterType(string $parameterTypeString, string $returnTypeAsString): string
    {
        if ($parameterTypeString !== '') {
            return '(' . $parameterTypeString . ')';
        }

        if ($returnTypeAsString !== 'mixed' && $returnTypeAsString !== '') {
            return '()';
        }

        return $parameterTypeString;
    }

    private function normalizeReturnType(string $parameterTypeString, string $returnTypeAsString): string
    {
        if ($returnTypeAsString === 'mixed' && $parameterTypeString === '') {
            return '';
        }

        return ':' . $returnTypeAsString;
    }
}
