<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp55\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Bool_;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PHPStan\Reflection\FunctionReflection;
use RectorPrefix20220606\PHPStan\Reflection\ParametersAcceptorSelector;
use RectorPrefix20220606\PHPStan\ShouldNotHappenException;
use RectorPrefix20220606\PHPStan\Type\CallableType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Reflection\ReflectionResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
            if ($functionLikeReflection === null) {
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
            $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs($funcCall->getAttribute(AttributeKey::SCOPE), $funcCall->args, $functionReflection->getVariants());
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
