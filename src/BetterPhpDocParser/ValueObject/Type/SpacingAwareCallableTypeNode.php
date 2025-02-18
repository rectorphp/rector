<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use Stringable;
final class SpacingAwareCallableTypeNode extends CallableTypeNode
{
    public function __toString() : string
    {
        // keep original (Psalm?) format, see https://github.com/rectorphp/rector/issues/2841
        return $this->createExplicitCallable();
    }
    private function createExplicitCallable() : string
    {
        $parameterTypeString = $this->createParameterTypeString();
        $returnTypeAsString = (string) $this->returnType;
        if (\strpos($returnTypeAsString, '|') !== \false) {
            $returnTypeAsString = '(' . $returnTypeAsString . ')';
        }
        $parameterTypeString = $this->normalizeParameterType($parameterTypeString, $returnTypeAsString);
        $returnTypeAsString = $this->normalizeReturnType($parameterTypeString, $returnTypeAsString);
        return \sprintf('%s%s%s', $this->identifier->name, $parameterTypeString, $returnTypeAsString);
    }
    private function createParameterTypeString() : string
    {
        $parameterTypeStrings = [];
        foreach ($this->parameters as $parameter) {
            $parameterTypeStrings[] = \trim((string) $parameter);
        }
        $parameterTypeString = \implode(', ', $parameterTypeStrings);
        return \trim($parameterTypeString);
    }
    private function normalizeParameterType(string $parameterTypeString, string $returnTypeAsString) : string
    {
        if ($parameterTypeString !== '') {
            return '(' . $parameterTypeString . ')';
        }
        if ($returnTypeAsString === 'mixed') {
            return $parameterTypeString;
        }
        if ($returnTypeAsString === '') {
            return $parameterTypeString;
        }
        return '()';
    }
    private function normalizeReturnType(string $parameterTypeString, string $returnTypeAsString) : string
    {
        if ($returnTypeAsString !== 'mixed') {
            return ':' . $returnTypeAsString;
        }
        if ($parameterTypeString !== '') {
            return ':' . $returnTypeAsString;
        }
        return '';
    }
}
