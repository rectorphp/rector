<?php

declare (strict_types=1);
namespace Rector\DowngradePhp55\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\CallableType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://externals.io/message/60337 https://www.php.net/manual/en/function.boolval.php
 *
 * @see \Rector\Tests\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector\DowngradeBoolvalRectorTest
 */
final class DowngradeBoolvalRector extends AbstractRector
{
    /**
     * @var string
     */
    private const BOOLVAL = 'boolval';
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace boolval() by type casting to boolean', [new CodeSample(<<<'CODE_SAMPLE'
$bool = boolval($value);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$bool = (bool) $value;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isName($node, self::BOOLVAL)) {
            return $this->refactorBoolval($node);
        }
        return $this->refactorAsCallback($node);
    }
    private function refactorBoolval(FuncCall $funcCall) : ?Bool_
    {
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return null;
        }
        return new Bool_($funcCall->args[0]->value);
    }
    private function refactorAsCallback(FuncCall $funcCall) : ?FuncCall
    {
        $functionLikeReflection = null;
        $refactored = \false;
        foreach ($funcCall->args as $position => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if (!$this->isBoolvalReference($arg)) {
                continue;
            }
            if (!$functionLikeReflection instanceof FunctionReflection) {
                $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($funcCall);
                if (!$functionLikeReflection instanceof FunctionReflection) {
                    break;
                }
            }
            $type = $this->getParameterType($functionLikeReflection, $funcCall, $position);
            if ($this->isCallable($type)) {
                $arg->value = $this->createBoolCastClosure();
                $refactored = \true;
            }
        }
        return $refactored ? $funcCall : null;
    }
    private function isBoolvalReference(Arg $arg) : bool
    {
        if (!$arg->value instanceof String_) {
            return \false;
        }
        return \strtolower($arg->value->value) === self::BOOLVAL;
    }
    private function getParameterType(FunctionReflection $functionReflection, FuncCall $funcCall, int $position) : ?Type
    {
        try {
            /** @var Scope $funcCallScope */
            $funcCallScope = $funcCall->getAttribute(AttributeKey::SCOPE);
            $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs($funcCallScope, $funcCall->getArgs(), $functionReflection->getVariants());
        } catch (ShouldNotHappenException $exception) {
            return null;
        }
        $parameters = $parametersAcceptor->getParameters();
        if (!isset($parameters[$position])) {
            return null;
        }
        return $parameters[$position]->getType();
    }
    private function isCallable(?Type $type) : bool
    {
        if (!$type instanceof Type) {
            return \false;
        }
        $trinaryLogic = $type->accepts(new CallableType(), \false);
        return $trinaryLogic->yes();
    }
    private function createBoolCastClosure() : Closure
    {
        $variable = new Variable('value');
        $closure = new Closure();
        $closure->params[] = new Param($variable);
        $closure->stmts[] = new Return_(new Bool_($variable));
        return $closure;
    }
}
