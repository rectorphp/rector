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
final class DowngradeBoolvalRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace boolval() by type casting to boolean', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isName($node, self::BOOLVAL)) {
            return $this->refactorBoolval($node);
        }
        return $this->refactorAsCallback($node);
    }
    private function refactorBoolval(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\Cast\Bool_
    {
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        return new \PhpParser\Node\Expr\Cast\Bool_($funcCall->args[0]->value);
    }
    private function refactorAsCallback(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        $functionLikeReflection = null;
        $refactored = \false;
        foreach ($funcCall->args as $position => $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            if (!$this->isBoolvalReference($arg)) {
                continue;
            }
            if ($functionLikeReflection === null) {
                $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($funcCall);
                if (!$functionLikeReflection instanceof \PHPStan\Reflection\FunctionReflection) {
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
    private function isBoolvalReference(\PhpParser\Node\Arg $arg) : bool
    {
        if (!$arg->value instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        return \strtolower($arg->value->value) === self::BOOLVAL;
    }
    private function getParameterType(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $funcCall, int $position) : ?\PHPStan\Type\Type
    {
        try {
            $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectFromArgs($funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE), $funcCall->args, $functionReflection->getVariants());
        } catch (\PHPStan\ShouldNotHappenException $exception) {
            return null;
        }
        $parameters = $parametersAcceptor->getParameters();
        if (!isset($parameters[$position])) {
            return null;
        }
        return $parameters[$position]->getType();
    }
    private function isCallable(?\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\Type) {
            return \false;
        }
        $trinaryLogic = $type->accepts(new \PHPStan\Type\CallableType(), \false);
        return $trinaryLogic->yes();
    }
    private function createBoolCastClosure() : \PhpParser\Node\Expr\Closure
    {
        $variable = new \PhpParser\Node\Expr\Variable('value');
        $closure = new \PhpParser\Node\Expr\Closure();
        $closure->params[] = new \PhpParser\Node\Param($variable);
        $closure->stmts[] = new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Expr\Cast\Bool_($variable));
        return $closure;
    }
}
