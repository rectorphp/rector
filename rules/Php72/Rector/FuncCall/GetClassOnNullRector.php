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
final class GetClassOnNullRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_NULL_ON_GET_CLASS;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Null is no more allowed in get_class()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$this->isName($node, 'get_class')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof Arg) {
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
        if (!$this->nodeTypeResolver->isNullableType($firstArgValue) && !$firstArgType instanceof NullType) {
            return null;
        }
        $notIdentical = new NotIdentical($firstArgValue, $this->nodeFactory->createNull());
        $funcCall = $this->createGetClassFuncCall($node);
        $selfClassConstFetch = $this->nodeFactory->createClassConstReference('self');
        return new Ternary($notIdentical, $funcCall, $selfClassConstFetch);
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        $isJustAdded = (bool) $funcCall->getAttribute(AttributeKey::DO_NOT_CHANGE);
        if ($isJustAdded) {
            return \true;
        }
        $classLike = $this->betterNodeFinder->findParentType($funcCall, Class_::class);
        if (!$classLike instanceof Class_) {
            return \true;
        }
        $parent = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Ternary) {
            if ($this->isIdenticalToNotNull($funcCall, $parent)) {
                return \true;
            }
            return $this->isNotIdenticalToNull($funcCall, $parent);
        }
        return \false;
    }
    private function createGetClassFuncCall(FuncCall $oldFuncCall) : FuncCall
    {
        $funcCall = new FuncCall($oldFuncCall->name, $oldFuncCall->args);
        $funcCall->setAttribute(AttributeKey::DO_NOT_CHANGE, \true);
        return $funcCall;
    }
    /**
     * E.g. "$value === [!null] ? get_class($value)"
     */
    private function isIdenticalToNotNull(FuncCall $funcCall, Ternary $ternary) : bool
    {
        if (!$ternary->cond instanceof Identical) {
            return \false;
        }
        if (!isset($funcCall->args[0])) {
            return \false;
        }
        if (!$funcCall->args[0] instanceof Arg) {
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
    private function isNotIdenticalToNull(FuncCall $funcCall, Ternary $ternary) : bool
    {
        if (!$ternary->cond instanceof NotIdentical) {
            return \false;
        }
        if (!isset($funcCall->args[0])) {
            return \false;
        }
        if (!$funcCall->args[0] instanceof Arg) {
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
