<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/laravel/framework/pull/27276
 * @see https://laravel.com/docs/5.8/upgrade#cache-ttl-in-seconds
 *
 * @see \Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\MinutesToSecondsInCacheRectorTest
 */
final class MinutesToSecondsInCacheRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const ATTRIBUTE_KEY_ALREADY_MULTIPLIED = 'already_multiplied';
    /**
     * @var string
     */
    private const PUT = 'put';
    /**
     * @var string
     */
    private const ADD = 'add';
    /**
     * @var string
     */
    private const REMEMBER = 'remember';
    /**
     * @var TypeToTimeMethodAndPosition[]
     */
    private $typeToTimeMethodsAndPositions = [];
    public function __construct()
    {
        $this->typeToTimeMethodsAndPositions = [new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Support\\Facades\\Cache', self::PUT, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Contracts\\Cache\\Repository', self::PUT, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Contracts\\Cache\\Store', self::PUT, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Contracts\\Cache\\Repository', self::ADD, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Contracts\\Cache\\Store', self::ADD, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Support\\Facades\\Cache', self::ADD, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Contracts\\Cache\\Repository', self::REMEMBER, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Support\\Facades\\Cache', self::REMEMBER, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Contracts\\Cache\\Store', self::REMEMBER, 2), new \Rector\Laravel\ValueObject\TypeToTimeMethodAndPosition('Illuminate\\Contracts\\Cache\\Store', 'putMany', 1)];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change minutes argument to seconds in Illuminate\\Contracts\\Cache\\Store and Illuminate\\Support\\Facades\\Cache', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Support\Facades\Cache::put('key', 'value', 60);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Support\Facades\Cache::put('key', 'value', 60 * 60);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->typeToTimeMethodsAndPositions as $typeToTimeMethodAndPosition) {
            if (!$this->isObjectType($node instanceof \PhpParser\Node\Expr\MethodCall ? $node->var : $node->class, $typeToTimeMethodAndPosition->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $typeToTimeMethodAndPosition->getMethodName())) {
                continue;
            }
            if (!isset($node->args[$typeToTimeMethodAndPosition->getPosition()])) {
                continue;
            }
            $argValue = $node->args[$typeToTimeMethodAndPosition->getPosition()]->value;
            return $this->processArgumentOnPosition($node, $argValue, $typeToTimeMethodAndPosition->getPosition());
        }
        return $node;
    }
    /**
     * @param StaticCall|MethodCall $node
     * @return StaticCall|MethodCall|null
     */
    private function processArgumentOnPosition(\PhpParser\Node $node, \PhpParser\Node\Expr $argExpr, int $argumentPosition) : ?\PhpParser\Node\Expr
    {
        if ($argExpr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            $this->refactorClassConstFetch($argExpr);
            return null;
        }
        if (!$this->nodeTypeResolver->isNumberType($argExpr)) {
            return null;
        }
        $mul = $this->mulByNumber($argExpr, 60);
        $node->args[$argumentPosition] = new \PhpParser\Node\Arg($mul);
        return $node;
    }
    private function refactorClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : void
    {
        $classConst = $this->nodeRepository->findClassConstByClassConstFetch($classConstFetch);
        if (!$classConst instanceof \PhpParser\Node\Stmt\ClassConst) {
            return;
        }
        $onlyConst = $classConst->consts[0];
        $alreadyMultiplied = (bool) $onlyConst->getAttribute(self::ATTRIBUTE_KEY_ALREADY_MULTIPLIED);
        if ($alreadyMultiplied) {
            return;
        }
        $onlyConst->value = $this->mulByNumber($onlyConst->value, 60);
        $onlyConst->setAttribute(self::ATTRIBUTE_KEY_ALREADY_MULTIPLIED, \true);
    }
    private function mulByNumber(\PhpParser\Node\Expr $argExpr, int $value) : \PhpParser\Node\Expr
    {
        if ($this->valueResolver->isValue($argExpr, 1)) {
            return new \PhpParser\Node\Scalar\LNumber($value);
        }
        return new \PhpParser\Node\Expr\BinaryOp\Mul($argExpr, new \PhpParser\Node\Scalar\LNumber($value));
    }
}
