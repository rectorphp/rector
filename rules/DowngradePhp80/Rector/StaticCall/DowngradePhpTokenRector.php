<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector\DowngradePhpTokenRectorTest
 */
final class DowngradePhpTokenRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const PHP_TOKEN = 'PhpToken';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('"something()" will be renamed to "somethingElse()"', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$tokens = \PhpToken::tokenize($code);

foreach ($tokens as $phpToken) {
   $name = $phpToken->getTokenName();
   $text = $phpToken->text;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$tokens = token_get_all($code);

foreach ($tokens as $token) {
    $name = is_array($token) ? token_name($token[0]) : null;
    $text = is_array($token) ? $token[1] : $token;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param StaticCall|MethodCall|PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->refactorStaticCall($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->refactorMethodCall($node);
        }
        return $this->refactorPropertyFetch($node);
    }
    private function refactorStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!$this->isObjectType($staticCall->class, new \PHPStan\Type\ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if (!$this->isName($staticCall->name, 'tokenize')) {
            return null;
        }
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('token_get_all'), $staticCall->args);
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\Ternary
    {
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'getTokenName')) {
            return null;
        }
        $isArrayFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('is_array'), [new \PhpParser\Node\Arg($methodCall->var)]);
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($methodCall->var, new \PhpParser\Node\Scalar\LNumber(0));
        $tokenGetNameFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('token_name'), [new \PhpParser\Node\Arg($arrayDimFetch)]);
        return new \PhpParser\Node\Expr\Ternary($isArrayFuncCall, $tokenGetNameFuncCall, $this->nodeFactory->createNull());
    }
    private function refactorPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : ?\PhpParser\Node\Expr\Ternary
    {
        if (!$this->isObjectType($propertyFetch->var, new \PHPStan\Type\ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if (!$this->isName($propertyFetch->name, 'text')) {
            return null;
        }
        $isArrayFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('is_array'), [new \PhpParser\Node\Arg($propertyFetch->var)]);
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($propertyFetch->var, new \PhpParser\Node\Scalar\LNumber(1));
        return new \PhpParser\Node\Expr\Ternary($isArrayFuncCall, $arrayDimFetch, $propertyFetch->var);
    }
}
