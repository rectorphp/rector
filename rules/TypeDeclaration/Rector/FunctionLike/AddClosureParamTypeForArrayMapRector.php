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
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(StaticTypeMapper $staticTypeMapper, ReflectionResolver $reflectionResolver)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Applies type hints to array_map closures', [new CodeSample(<<<'CODE_SAMPLE'
array_map(function ($value) {
    return strlen($value);
}, $strings);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
array_map(function (string $value) {
    return strlen($value);
}, $strings);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
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
        // array_map passes the value of each array positionally to the closure:
        // the i-th closure param receives values from the i-th array argument.
        $arrayArgs = array_slice($node->args, 1);
        $closure = $args[0]->value;
        $changed = \false;
        foreach ($arrayArgs as $position => $arg) {
            // spread (...$arrays) cannot be mapped positionally, bail out
            if (!$arg instanceof Arg || $arg->unpack) {
                return null;
            }
            $param = $closure->params[$position] ?? null;
            if (!$param instanceof Param) {
                continue;
            }
            $arrayType = $this->getType($arg->value);
            if (!$arrayType instanceof ArrayType) {
                continue;
            }
            $valueType = $arrayType->getIterableValueType();
            if ($valueType instanceof MixedType) {
                continue;
            }
            if ($this->refactorParameter($param, $valueType)) {
                $changed = \true;
            }
        }
        if ($changed) {
            return $node;
        }
        return null;
    }
    private function refactorParameter(Param $param, Type $type): bool
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
}
