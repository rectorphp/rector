<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\FamilyTree\NodeAnalyzer\PropertyUsageAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\MoveInjectToExistingConstructorRectorTest
 */
final class MoveInjectToExistingConstructorRector extends AbstractRector
{
    /**
     * @var PropertyUsageAnalyzer
     */
    private $propertyUsageAnalyzer;

    public function __construct(PropertyUsageAnalyzer $propertyUsageAnalyzer)
    {
        $this->propertyUsageAnalyzer = $propertyUsageAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move @inject properties to constructor, if there already is one',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;

    /**
     * @var OtherDependency
     */
    private $otherDependency;

    public function __construct(OtherDependency $otherDependency)
    {
        $this->otherDependency = $otherDependency;
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    /**
     * @var OtherDependency
     */
    private $otherDependency;

    public function __construct(OtherDependency $otherDependency, SomeDependency $someDependency)
    {
        $this->otherDependency = $otherDependency;
        $this->someDependency = $someDependency;
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $injectProperties = $this->getInjectProperties($node);
        if ($injectProperties === []) {
            return null;
        }

        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return null;
        }

        foreach ($injectProperties as $injectProperty) {
            $this->removeInjectAnnotation($injectProperty);
            $this->changePropertyVisibility($injectProperty);
            $this->addPropertyToCollector($injectProperty);

            if ($this->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
                $this->removeNode($injectProperty);
            }
        }

        return $node;
    }

    /**
     * @return Property[]
     */
    private function getInjectProperties(Class_ $class): array
    {
        return array_filter($class->getProperties(), function (Property $property): bool {
            return $this->isInjectProperty($property);
        });
    }

    private function removeInjectAnnotation(Property $property): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $phpDocInfo->removeByType(NetteInjectTagNode::class);
    }

    private function changePropertyVisibility(Property $injectProperty): void
    {
        if ($this->propertyUsageAnalyzer->isPropertyFetchedInChildClass($injectProperty)) {
            $this->visibilityManipulator->makeProtected($injectProperty);
        } else {
            $this->visibilityManipulator->makePrivate($injectProperty);
        }
    }

    private function isInjectProperty(Property $property): bool
    {
        if (! $property->isPublic()) {
            return false;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->hasByType(NetteInjectTagNode::class);
    }
}
