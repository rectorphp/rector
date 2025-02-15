<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Param;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Type\ArrayType;
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
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayMapRector\AddClosureParamTypeForArrayMapRectorTest
 */
final class AddClosureParamTypeForArrayMapRector extends AbstractRector
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
array_map(function ($value, $key): string {
    return $value . $key;
}, $strings);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
array_map(function (string $value, int $key): bool {
    return $value . $key;
}, $strings);
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
        if (!$this->isName($node, 'array_map')) {
            return null;
        }
        $funcReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$funcReflection instanceof NativeFunctionReflection) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0]) || !$args[0]->value instanceof Closure) {
            return null;
        }
        /** @var ArrayType[] $types */
        $types = \array_filter(\array_map(function ($arg) : ?ArrayType {
            if (!$arg instanceof Arg) {
                return null;
            }
            $type = $this->getType($arg->value);
            if ($type instanceof ArrayType) {
                return $type;
            }
            return null;
        }, \array_slice($node->args, 1)));
        $values = [];
        $keys = [];
        foreach ($types as $type) {
            $values[] = $type->getIterableValueType();
            $keys[] = $type->getIterableKeyType();
        }
        foreach ($values as $value) {
            if ($value instanceof MixedType) {
                $values = [];
                break;
            } elseif ($value instanceof UnionType) {
                $values = \array_merge($values, $value->getTypes());
            }
        }
        foreach ($keys as $key) {
            if ($key instanceof MixedType) {
                $keys = [];
                break;
            } elseif ($key instanceof UnionType) {
                $keys = \array_merge($keys, $key->getTypes());
            }
        }
        $filter = fn(Type $type): bool => !$type instanceof UnionType;
        $valueType = $this->combineTypes(\array_filter($values, $filter));
        $keyType = $this->combineTypes(\array_filter($keys, $filter));
        if (!$keyType instanceof Type && !$valueType instanceof Type) {
            return null;
        }
        if ($this->updateClosureWithTypes($args[0]->value, $keyType, $valueType)) {
            return $node;
        }
        return null;
    }
    private function updateClosureWithTypes(Closure $closure, ?Type $keyType, ?Type $valueType) : bool
    {
        $changes = \false;
        $valueParam = $closure->params[0] ?? null;
        $keyParam = $closure->params[1] ?? null;
        if ($valueParam instanceof Param && $valueType instanceof Type && $this->refactorParameter($closure->params[0], $valueType)) {
            $changes = \true;
        }
        if ($keyParam instanceof Param && $keyType instanceof Type && $this->refactorParameter($closure->params[1], $keyType)) {
            return \true;
        }
        return $changes;
    }
    private function refactorParameter(Param $param, Type $type) : bool
    {
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
        return new UnionType(UnionTypeHelper::sortTypes($types));
    }
}
