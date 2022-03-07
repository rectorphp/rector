<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md#security
 * @see \Rector\Symfony\Tests\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector\AuthorizationCheckerIsGrantedExtractorRectorTest
 */
final class AuthorizationCheckerIsGrantedExtractorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Extract $this->authorizationChecker->isGranted([$a, $b]) to $this->authorizationChecker->isGranted($a) || $this->authorizationChecker->isGranted($b)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
if ($this->authorizationChecker->isGranted(['ROLE_USER', 'ROLE_ADMIN'])) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if ($this->authorizationChecker->isGranted('ROLE_USER') || $this->authorizationChecker->isGranted('ROLE_USER')) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\MethodCall|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $objectType = $this->nodeTypeResolver->getType($node->var);
        if (!$objectType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        $authorizationChecker = new \PHPStan\Type\ObjectType('Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface');
        if (!$authorizationChecker->isSuperTypeOf($objectType)->yes()) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node->name, 'isGranted')) {
            return null;
        }
        $args = $node->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        if (!isset($args[0])) {
            return null;
        }
        $value = $args[0]->value;
        if (!$value instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        return $this->processExtractIsGranted($node, $value, $args);
    }
    /**
     * @param Arg[] $args
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\MethodCall|null
     */
    private function processExtractIsGranted(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\Array_ $array, array $args)
    {
        $exprs = [];
        foreach ($array->items as $item) {
            if ($item instanceof \PhpParser\Node\Expr\ArrayItem) {
                $exprs[] = $item->value;
            }
        }
        if ($exprs === []) {
            return null;
        }
        $args[0]->value = $exprs[0];
        $methodCall->args = $args;
        if (\count($exprs) === 1) {
            return $methodCall;
        }
        $rightMethodCall = clone $methodCall;
        $rightMethodCall->args[0] = new \PhpParser\Node\Arg($exprs[1]);
        $newMethodCallRight = new \PhpParser\Node\Expr\MethodCall($methodCall->var, $methodCall->name, $rightMethodCall->args, $methodCall->getAttributes());
        $booleanOr = new \PhpParser\Node\Expr\BinaryOp\BooleanOr($methodCall, $newMethodCallRight);
        foreach ($exprs as $key => $expr) {
            if ($key <= 1) {
                continue;
            }
            $rightMethodCall = clone $methodCall;
            $rightMethodCall->args[0] = new \PhpParser\Node\Arg($expr);
            $newMethodCallRight = new \PhpParser\Node\Expr\MethodCall($methodCall->var, $methodCall->name, $rightMethodCall->args, $methodCall->getAttributes());
            $booleanOr = new \PhpParser\Node\Expr\BinaryOp\BooleanOr($booleanOr, $newMethodCallRight);
        }
        return $booleanOr;
    }
}
