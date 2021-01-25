<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Rector\Naming\PropertyRenamer\MatchTypePropertyRenamer;
use Rector\Naming\PropertyRenamer\PropertyFetchRenamer;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\RenamePropertyToMatchTypeRectorTest
 */
final class RenamePropertyToMatchTypeRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = false;

    /**
     * @var PropertyRenameFactory
     */
    private $propertyRenameFactory;

    /**
     * @var MatchTypePropertyRenamer
     */
    private $matchTypePropertyRenamer;

    /**
     * @var MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;

    /**
     * @var MatchParamTypeExpectedNameResolver
     */
    private $matchParamTypeExpectedNameResolver;

    /**
     * @var PropertyFetchRenamer
     */
    private $propertyFetchRenamer;

    public function __construct(
        MatchTypePropertyRenamer $matchTypePropertyRenamer,
        PropertyRenameFactory $propertyRenameFactory,
        MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver,
        MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver,
        PropertyFetchRenamer $propertyFetchRenamer
    ) {
        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->matchTypePropertyRenamer = $matchTypePropertyRenamer;
        $this->matchPropertyTypeExpectedNameResolver = $matchPropertyTypeExpectedNameResolver;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
        $this->propertyFetchRenamer = $propertyFetchRenamer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename property and method param to match its type',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Interface_::class];
    }

    /**
     * @param Class_|Interface_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->refactorClassProperties($node);
        $this->renamePropertyPromotion($node);

        if (! $this->hasChanged) {
            return null;
        }

        return $node;
    }

    private function refactorClassProperties(ClassLike $classLike): void
    {
        foreach ($classLike->getProperties() as $property) {
            $propertyRename = $this->propertyRenameFactory->create(
                $property,
                $this->matchPropertyTypeExpectedNameResolver
            );
            if (! $propertyRename instanceof PropertyRename) {
                continue;
            }

            $renameProperty = $this->matchTypePropertyRenamer->rename($propertyRename);
            if (! $renameProperty instanceof Property) {
                continue;
            }

            $this->hasChanged = true;
        }
    }

    private function renamePropertyPromotion(ClassLike $classLike): void
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            return;
        }

        $constructClassMethod = $classLike->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return;
        }

        foreach ($constructClassMethod->params as $param) {
            if ($param->flags === 0) {
                continue;
            }

            // promoted property
            $desiredPropertyName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($desiredPropertyName === null) {
                continue;
            }

            $currentName = $this->getName($param);
            $this->propertyFetchRenamer->renamePropertyFetchesInClass($classLike, $currentName, $desiredPropertyName);

            $param->var->name = $desiredPropertyName;
        }
    }
}
