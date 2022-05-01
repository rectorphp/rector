<?php

declare (strict_types=1);
namespace Rector\Visibility\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Visibility;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector\ChangeMethodVisibilityRectorTest
 */
final class ChangeMethodVisibilityRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var ChangeMethodVisibility[]
     */
    private $methodVisibilities = [];
    /**
     * @readonly
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->parentClassScopeResolver = $parentClassScopeResolver;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change visibility of method from parent class.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected function someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class FrameworkClass
{
    protected function someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    protected function someMethod()
    {
    }
}
CODE_SAMPLE
, [new \Rector\Visibility\ValueObject\ChangeMethodVisibility('FrameworkClass', 'someMethod', \Rector\Core\ValueObject\Visibility::PROTECTED)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $parentClassName = $this->parentClassScopeResolver->resolveParentClassName($scope);
        if ($parentClassName === null) {
            return null;
        }
        foreach ($this->methodVisibilities as $methodVisibility) {
            if ($methodVisibility->getClass() !== $parentClassName) {
                continue;
            }
            if (!$this->isName($node, $methodVisibility->getMethod())) {
                continue;
            }
            $this->visibilityManipulator->changeNodeVisibility($node, $methodVisibility->getVisibility());
            return $node;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Visibility\ValueObject\ChangeMethodVisibility::class);
        $this->methodVisibilities = $configuration;
    }
}
