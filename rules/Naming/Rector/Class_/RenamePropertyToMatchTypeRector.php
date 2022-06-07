<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Rector\Naming\PropertyRenamer\MatchTypePropertyRenamer;
use Rector\Naming\PropertyRenamer\PropertyPromotionRenamer;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\RenamePropertyToMatchTypeRectorTest
 */
final class RenamePropertyToMatchTypeRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\Naming\PropertyRenamer\MatchTypePropertyRenamer
     */
    private $matchTypePropertyRenamer;
    /**
     * @readonly
     * @var \Rector\Naming\ValueObjectFactory\PropertyRenameFactory
     */
    private $propertyRenameFactory;
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\PropertyRenamer\PropertyPromotionRenamer
     */
    private $propertyPromotionRenamer;
    public function __construct(MatchTypePropertyRenamer $matchTypePropertyRenamer, PropertyRenameFactory $propertyRenameFactory, MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver, PropertyPromotionRenamer $propertyPromotionRenamer)
    {
        $this->matchTypePropertyRenamer = $matchTypePropertyRenamer;
        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->matchPropertyTypeExpectedNameResolver = $matchPropertyTypeExpectedNameResolver;
        $this->propertyPromotionRenamer = $propertyPromotionRenamer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename property and method param to match its type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $eventManager;

    public function __construct(EntityManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        return [Class_::class, Interface_::class];
    }
    /**
     * @param Class_|Interface_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->refactorClassProperties($node);
        $this->propertyPromotionRenamer->renamePropertyPromotion($node);
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function refactorClassProperties(ClassLike $classLike) : void
    {
        foreach ($classLike->getProperties() as $property) {
            $expectedPropertyName = $this->matchPropertyTypeExpectedNameResolver->resolve($property);
            if ($expectedPropertyName === null) {
                continue;
            }
            $propertyRename = $this->propertyRenameFactory->createFromExpectedName($property, $expectedPropertyName);
            if (!$propertyRename instanceof PropertyRename) {
                continue;
            }
            $renameProperty = $this->matchTypePropertyRenamer->rename($propertyRename);
            if (!$renameProperty instanceof Property) {
                continue;
            }
            $this->hasChanged = \true;
        }
    }
}
