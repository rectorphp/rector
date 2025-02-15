<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Param;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Type\ClosureType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\UnionTypeHelper;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayReduceRector\AddClosureParamTypeForArrayReduceRectorTest
 */
final class AddClosureParamTypeForArrayReduceRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, ReflectionResolver $reflectionResolver)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Applies type hints to array_map closures', [new CodeSample(<<<'CODE_SAMPLE'
array_reduce($strings, function ($carry, $value, $key): string {
    return $carry . $value;
}, $initialString);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
array_reduce($strings, function (string $carry, string $value): string {
    return $carry . $value;
}, $initialString);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'array_reduce')) {
            return null;
        }
        $funcReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$funcReflection instanceof NativeFunctionReflection) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[1]) || !$args[1]->value instanceof Closure) {
            return null;
        }
        $closureType = $this->getType($args[1]->value);
        if (!$closureType instanceof ClosureType) {
            return null;
        }
        $carryType = $closureType->getReturnType();
        if (isset($args[2])) {
            $carryType = $this->combineTypes([$this->getType($args[2]->value), $carryType]);
        }
        $type = $this->getType($args[0]->value);
        $valueType = $type->getIterableValueType();
        if ($this->updateClosureWithTypes($args[1]->value, $valueType, $carryType)) {
            return $node;
        }
        return null;
    }
    private function updateClosureWithTypes(Closure $closure, ?Type $valueType, ?Type $carryType) : bool
    {
        $changes = \false;
        $carryParam = $closure->params[0] ?? null;
        $valueParam = $closure->params[1] ?? null;
        if ($valueParam instanceof Param && $valueType instanceof Type && $this->refactorParameter($valueParam, $valueType)) {
            $changes = \true;
        }
        if ($carryParam instanceof Param && $carryType instanceof Type && $this->refactorParameter($carryParam, $carryType)) {
            return \true;
        }
        return $changes;
    }
    private function refactorParameter(Param $param, Type $type) : bool
    {
        if ($type instanceof MixedType) {
            return \false;
        }
        // already set → no change
        if ($param->type instanceof Node) {
            return \false;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
        if (!$paramTypeNode instanceof Node) {
            return \false;
        }
        $param->type = $paramTypeNode;
        return \true;
    }
    /**
     * @param Type[] $types
     */
    private function combineTypes(array $types) : ?Type
    {
        if ($types === []) {
            return null;
        }
        $types = \array_reduce($types, function (array $types, Type $type) : array {
            foreach ($types as $previousType) {
                if ($this->typeComparator->areTypesEqual($type, $previousType)) {
                    return $types;
                }
            }
            $types[] = $type;
            return $types;
        }, []);
        if (\count($types) === 1) {
            return $types[0];
        }
        foreach ($types as $type) {
            if ($type instanceof UnionType) {
                foreach ($type->getTypes() as $unionedType) {
                    if ($unionedType instanceof IntersectionType) {
                        return null;
                    }
                }
            }
        }
        return new UnionType(UnionTypeHelper::sortTypes($types));
    }
}
