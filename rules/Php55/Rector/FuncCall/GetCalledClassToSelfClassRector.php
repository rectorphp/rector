<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use Rector\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ClassModifierChecker;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector\GetCalledClassToSelfClassRectorTest
 */
final class GetCalledClassToSelfClassRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ClassModifierChecker $classModifierChecker;
    public function __construct(ClassModifierChecker $classModifierChecker)
    {
        $this->classModifierChecker = $classModifierChecker;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `get_called_class()` to `self::class` on final class', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
   public function callOnMe()
   {
       var_dump(get_called_class());
   }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
   public function callOnMe()
   {
       var_dump(self::class);
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
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'get_called_class')) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        if (!$scope->isInClass()) {
            return null;
        }
        if ($this->classModifierChecker->isInsideFinalClass($node)) {
            return $this->nodeFactory->createClassConstFetch(ObjectReference::SELF, 'class');
        }
        if ($scope->isInAnonymousFunction()) {
            return $this->nodeFactory->createClassConstFetch(ObjectReference::SELF, 'class');
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CLASSNAME_CONSTANT;
    }
}
