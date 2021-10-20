<?php

declare (strict_types=1);
namespace Rector\Visibility\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Visibility;
use Rector\Visibility\ValueObject\ChangeConstantVisibility;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector\ChangeConstantVisibilityRectorTest
 */
final class ChangeConstantVisibilityRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_CONSTANT_VISIBILITY_CHANGES = 'class_constant_visibility_changes';
    /**
     * @var ChangeConstantVisibility[]
     */
    private $classConstantVisibilityChanges = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change visibility of constant from parent class.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [self::CLASS_CONSTANT_VISIBILITY_CHANGES => [new \Rector\Visibility\ValueObject\ChangeConstantVisibility('ParentObject', 'SOME_CONSTANT', \Rector\Core\ValueObject\Visibility::PROTECTED)]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassConst::class];
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
    public function configure(array $configuration) : void
    {
        $classConstantVisibilityChanges = $configuration[self::CLASS_CONSTANT_VISIBILITY_CHANGES] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($classConstantVisibilityChanges, \Rector\Visibility\ValueObject\ChangeConstantVisibility::class);
        $this->classConstantVisibilityChanges = $classConstantVisibilityChanges;
    }
}
