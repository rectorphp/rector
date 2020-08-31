<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\FamilyTree\NodeAnalyzer\PropertyUsageAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move @inject properties to constructor, if there already is one', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP
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
        if ($constructClassMethod === null) {
            return null;
        }

        foreach ($injectProperties as $injectProperty) {
            $this->removeInjectAnnotation($injectProperty);

            $this->changePropertyVisibility($injectProperty);

            $this->addPropertyToCollector($injectProperty);
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
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->removeByName('inject');
    }

    private function changePropertyVisibility(Property $injectProperty): void
    {
        if ($this->propertyUsageAnalyzer->isPropertyFetchedInChildClass($injectProperty)) {
            $this->makeProtected($injectProperty);
        } else {
            $this->makePrivate($injectProperty);
        }
    }

    private function isInjectProperty(Property $property): bool
    {
        if (! $property->isPublic()) {
            return false;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return (bool) $phpDocInfo->getTagsByName('inject');
    }
}
