<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Removing\NodeManipulator\ComplexNodeRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\RemoveUnusedPrivatePropertyRectorTest
 */
final class RemoveUnusedPrivatePropertyRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const REMOVE_ASSIGN_SIDE_EFFECT = 'remove_assign_side_effect';
    /**
     * Default to true, which apply remove assign even has side effect.
     * Set to false will allow to skip when assign has side effect.
     * @var bool
     */
    private $removeAssignSideEffect = \true;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Removing\NodeManipulator\ComplexNodeRemover
     */
    private $complexNodeRemover;
    public function __construct(PropertyManipulator $propertyManipulator, ComplexNodeRemover $complexNodeRemover)
    {
        $this->propertyManipulator = $propertyManipulator;
        $this->complexNodeRemover = $complexNodeRemover;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->removeAssignSideEffect = $configuration[self::REMOVE_ASSIGN_SIDE_EFFECT] ?? (bool) \current($configuration);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused private properties', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $property;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
, [self::REMOVE_ASSIGN_SIDE_EFFECT => \true])]);
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
    public function refactor(Node $node) : ?Node
    {
        $hasRemoved = \false;
        foreach ($node->stmts as $key => $property) {
            if (!$property instanceof Property) {
                continue;
            }
            if ($this->shouldSkipProperty($property)) {
                continue;
            }
            if ($this->propertyManipulator->isPropertyUsedInReadContext($node, $property)) {
                continue;
            }
            // use different variable to avoid re-assign back $hasRemoved to false
            // when already asssigned to true
            $isRemoved = $this->complexNodeRemover->removePropertyAndUsages($node, $property, $this->removeAssignSideEffect);
            if ($isRemoved) {
                $this->processRemoveSameLineComment($node, $property, $key);
                $hasRemoved = \true;
            }
        }
        return $hasRemoved ? $node : null;
    }
    private function processRemoveSameLineComment(Class_ $class, Property $property, int $key) : void
    {
        if (!isset($class->stmts[$key + 1])) {
            return;
        }
        if (!$class->stmts[$key + 1] instanceof Nop) {
            return;
        }
        if ($class->stmts[$key + 1]->getEndLine() !== $property->getStartLine()) {
            return;
        }
        unset($class->stmts[$key + 1]);
    }
    private function shouldSkipProperty(Property $property) : bool
    {
        if (\count($property->props) !== 1) {
            return \true;
        }
        return !$property->isPrivate();
    }
}
