<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class ParamTypeCompatibilityChecker
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var TypeComparator
     */
    private $typeComparator;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        StaticTypeMapper $staticTypeMapper,
        TypeComparator $typeComparator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
    }

    public function isCompatibleWithParamStrictTyped(Arg $arg, Type $argValueType): bool
    {
        // cannot verify, assume good
        if (! $arg->value instanceof Variable) {
            return true;
        }

        $argumentVariableName = $this->nodeNameResolver->getName($arg->value);

        $classMethod = $arg->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        foreach ($classMethod->getParams() as $param) {
            if (! $this->nodeNameResolver->isName($param->var, $argumentVariableName)) {
                continue;
            }

            if ($param->type === null) {
                // docblock defined, remove it
                return false;
            }

            $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            return $this->typeComparator->areTypesEqual($paramType, $argValueType);
        }

        return true;
    }
}
