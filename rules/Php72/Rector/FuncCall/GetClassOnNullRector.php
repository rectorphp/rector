<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$hasChanged) {
            if ($node instanceof Ternary) {
                return NodeTraverser::STOP_TRAVERSAL;
            }
            if (!$node instanceof FuncCall) {
                return null;
            }
            // just created func call
            if ($node->getAttribute(AttributeKey::DO_NOT_CHANGE) === \true) {
                return null;
            }
            if (!$this->isName($node, 'get_class')) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $firstArg = $node->getArgs()[0] ?? null;
            if (!$firstArg instanceof Arg) {
                return null;
            }
            $firstArgValue = $firstArg->value;
            $firstArgType = $this->getType($firstArgValue);
            if (!$this->nodeTypeResolver->isNullableType($firstArgValue) && !$firstArgType instanceof NullType) {
                return null;
            }
            $notIdentical = new NotIdentical($firstArgValue, $this->nodeFactory->createNull());
            $funcCall = $this->createGetClassFuncCall($node);
            $selfClassConstFetch = $this->nodeFactory->createClassConstReference('self');
            $hasChanged = \true;
            return new Ternary($notIdentical, $funcCall, $selfClassConstFetch);
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function createGetClassFuncCall(FuncCall $oldFuncCall) : FuncCall
    {
        $funcCall = new FuncCall($oldFuncCall->name, $oldFuncCall->args);
        $funcCall->setAttribute(AttributeKey::DO_NOT_CHANGE, \true);
        return $funcCall;
    }
}
