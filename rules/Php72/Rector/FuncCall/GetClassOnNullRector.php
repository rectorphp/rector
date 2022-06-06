<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\NullType;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://php.net/manual/en/migration72.incompatible.php#migration72.incompatible.no-null-to-get_class https://3v4l.org/sk0fp
 *
 * @see \Rector\Tests\Php72\Rector\FuncCall\GetClassOnNullRector\GetClassOnNullRectorTest
 */
final class GetClassOnNullRector extends \Rector\Core\Rector\AbstractScopeAwareRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NO_NULL_ON_GET_CLASS;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Null is no more allowed in get_class()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getItem()
    {
        $value = null;
        return get_class($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getItem()
    {
        $value = null;
        return $value !== null ? get_class($value) : self::class;
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'get_class')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $firstArgValue = $node->args[0]->value;
        if (!$scope->isInClass()) {
            return null;
        }
        // possibly already changed
        if ($this->shouldSkip($node)) {
            return null;
        }
        $firstArgType = $this->getType($firstArgValue);
        if (!$this->nodeTypeResolver->isNullableType($firstArgValue) && !$firstArgType instanceof \PHPStan\Type\NullType) {
            return null;
        }
        $notIdentical = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($firstArgValue, $this->nodeFactory->createNull());
        $funcCall = $this->createGetClassFuncCall($node);
        $selfClassConstFetch = $this->nodeFactory->createClassConstReference('self');
        return new \PhpParser\Node\Expr\Ternary($notIdentical, $funcCall, $selfClassConstFetch);
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        $isJustAdded = (bool) $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::DO_NOT_CHANGE);
        if ($isJustAdded) {
            return \true;
        }
        $classLike = $this->betterNodeFinder->findParentType($funcCall, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \true;
        }
        $parent = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Expr\Ternary) {
            if ($this->isIdenticalToNotNull($funcCall, $parent)) {
                return \true;
            }
            return $this->isNotIdenticalToNull($funcCall, $parent);
        }
        return \false;
    }
    private function createGetClassFuncCall(\PhpParser\Node\Expr\FuncCall $oldFuncCall) : \PhpParser\Node\Expr\FuncCall
    {
        $funcCall = new \PhpParser\Node\Expr\FuncCall($oldFuncCall->name, $oldFuncCall->args);
        $funcCall->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::DO_NOT_CHANGE, \true);
        return $funcCall;
    }
    /**
     * E.g. "$value === [!null] ? get_class($value)"
     */
    private function isIdenticalToNotNull(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\Ternary $ternary) : bool
    {
        if (!$ternary->cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return \false;
        }
        if (!isset($funcCall->args[0])) {
            return \false;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        if ($this->nodeComparator->areNodesEqual($ternary->cond->left, $funcCall->args[0]->value) && !$this->valueResolver->isNull($ternary->cond->right)) {
            return \true;
        }
        if (!$this->nodeComparator->areNodesEqual($ternary->cond->right, $funcCall->args[0]->value)) {
            return \false;
        }
        return !$this->valueResolver->isNull($ternary->cond->left);
    }
    /**
     * E.g. "$value !== null ? get_class($value)"
     */
    private function isNotIdenticalToNull(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\Ternary $ternary) : bool
    {
        if (!$ternary->cond instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
            return \false;
        }
        if (!isset($funcCall->args[0])) {
            return \false;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        if ($this->nodeComparator->areNodesEqual($ternary->cond->left, $funcCall->args[0]->value) && $this->valueResolver->isNull($ternary->cond->right)) {
            return \true;
        }
        if (!$this->nodeComparator->areNodesEqual($ternary->cond->right, $funcCall->args[0]->value)) {
            return \false;
        }
        return $this->valueResolver->isNull($ternary->cond->left);
    }
}
