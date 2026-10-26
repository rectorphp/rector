<?php

declare (strict_types=1);
namespace Rector\Visibility\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\Visibility;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Visibility\Rector\Class_\ChangeMethodVisibilityRector\ChangeMethodVisibilityRectorTest
 */
final class ChangeMethodVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @var ChangeMethodVisibility[]
     */
    private array $methodVisibilities = [];
    public function __construct(VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change visibility of method from parent class', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [new ChangeMethodVisibility('FrameworkClass', 'someMethod', Visibility::PROTECTED)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->methodVisibilities === []) {
            return null;
        }
        $classReflection = $this->fetchClassLikeReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof ClassReflection) {
            return null;
        }
        $parentClassName = $parentClassReflection->getName();
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            foreach ($this->methodVisibilities as $methodVisibility) {
                if ($methodVisibility->getClass() !== $parentClassName) {
                    continue;
                }
                if (!$this->isName($classMethod, $methodVisibility->getMethod())) {
                    continue;
                }
                $this->visibilityManipulator->changeNodeVisibility($classMethod, $methodVisibility->getVisibility());
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, ChangeMethodVisibility::class);
        $this->methodVisibilities = $configuration;
    }
}
