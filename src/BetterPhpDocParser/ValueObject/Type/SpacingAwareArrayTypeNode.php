<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper;
use Stringable;
final class SpacingAwareArrayTypeNode extends ArrayTypeNode
{
    public function __toString() : string
    {
        if ($this->type instanceof CallableTypeNode) {
            return \sprintf('(%s)[]', (string) $this->type);
        }
        $typeAsString = (string) $this->type;
        if ($this->isGenericArrayCandidate($this->type)) {
            return \sprintf('array<%s>', $typeAsString);
        }
        if ($this->type instanceof ArrayTypeNode) {
            return $this->printArrayType($this->type);
        }
        if ($this->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            return $this->printUnionType($this->type);
        }
        return $typeAsString . '[]';
    }
    private function isGenericArrayCandidate(TypeNode $typeNode) : bool
    {
        $hasGenericTypeParent = (bool) $this->getAttribute(ArrayTypeMapper::HAS_GENERIC_TYPE_PARENT);
        if (!$hasGenericTypeParent) {
            return \false;
        }
        return $typeNode instanceof UnionTypeNode || $typeNode instanceof ArrayTypeNode;
    }
    private function printArrayType(ArrayTypeNode $arrayTypeNode) : string
    {
        $typeAsString = (string) $arrayTypeNode;
        $singleTypesAsString = \explode('|', $typeAsString);
        foreach ($singleTypesAsString as $key => $singleTypeAsString) {
            $singleTypesAsString[$key] = $singleTypeAsString . '[]';
        }
        return \implode('|', $singleTypesAsString);
    }
    private function printUnionType(\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : string
    {
        $unionedTypes = [];
        if ($bracketsAwareUnionTypeNode->isWrappedInBrackets()) {
            return $bracketsAwareUnionTypeNode . '[]';
        }
        foreach ($bracketsAwareUnionTypeNode->types as $unionedType) {
            $unionedTypes[] = $unionedType . '[]';
        }
        return \implode('|', $unionedTypes);
    }
}
