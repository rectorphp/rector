<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class ClassMethodParamTypeCompleter
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * @param array<int, Type> $classParameterTypes
     */
    public function complete(ClassMethod $classMethod, array $classParameterTypes): ?ClassMethod
    {
        $hasChanged = false;

        foreach ($classParameterTypes as $position => $argumentStaticType) {
            if ($this->shouldSkipArgumentStaticType($classMethod, $argumentStaticType, $position)) {
                continue;
            }

            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($argumentStaticType);
            if ($phpParserTypeNode === null) {
                continue;
            }

            // update parameter
            $classMethod->params[$position]->type = $phpParserTypeNode;
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $classMethod;
        }

        return null;
    }

    private function shouldSkipArgumentStaticType(
        ClassMethod $classMethod,
        Type $argumentStaticType,
        int $position
    ): bool {
        if ($argumentStaticType instanceof MixedType) {
            return true;
        }

        if (! isset($classMethod->params[$position])) {
            return true;
        }

        $parameter = $classMethod->params[$position];
        if ($parameter->type === null) {
            return false;
        }

        $parameterStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($parameter->type);
        // already completed → skip
        return $parameterStaticType->equals($argumentStaticType);
    }
}
