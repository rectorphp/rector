<?php

declare (strict_types=1);
namespace Rector\Visibility\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Visibility;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Visibility\ValueObject\ChangeConstantVisibility;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector\ChangeConstantVisibilityRectorTest
 */
final class ChangeConstantVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ChangeConstantVisibility[]
     */
    private $classConstantVisibilityChanges = [];
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change visibility of constant from parent class.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    public const SOME_CONSTANT = 1;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}
CODE_SAMPLE
, [new ChangeConstantVisibility('ParentObject', 'SOME_CONSTANT', Visibility::PROTECTED)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConst::class];
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->classConstantVisibilityChanges as $classConstantVisibilityChange) {
            if (!$this->isObjectType($node, $classConstantVisibilityChange->getObjectType())) {
                continue;
            }
            if (!$this->isName($node, $classConstantVisibilityChange->getConstant())) {
                continue;
            }
            $this->visibilityManipulator->changeNodeVisibility($node, $classConstantVisibilityChange->getVisibility());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ChangeConstantVisibility::class);
        $this->classConstantVisibilityChanges = $configuration;
    }
}
