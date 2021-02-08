<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Type;
use Rector\CodeQuality\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\CodeQuality\NodeAnalyzer\LocalPropertyAnalyzer;
use Rector\CodeQuality\NodeFactory\MissingPropertiesFactory;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/GL6II
 * @see https://3v4l.org/eTrhZ
 * @see https://3v4l.org/C554W
 *
 * @see \Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\CompleteDynamicPropertiesRectorTest
 */
final class CompleteDynamicPropertiesRector extends AbstractRector
{
    /**
     * @var MissingPropertiesFactory
     */
    private $missingPropertiesFactory;

    /**
     * @var LocalPropertyAnalyzer
     */
    private $localPropertyAnalyzer;

    /**
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    public function __construct(
        MissingPropertiesFactory $missingPropertiesFactory,
        LocalPropertyAnalyzer $localPropertyAnalyzer,
        ClassLikeAnalyzer $classLikeAnalyzer
    ) {
        $this->missingPropertiesFactory = $missingPropertiesFactory;
        $this->localPropertyAnalyzer = $localPropertyAnalyzer;
        $this->classLikeAnalyzer = $classLikeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add missing dynamic properties', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function set()
    {
        $this->value = 5;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int
     */
    public $value;
    public function set()
    {
        $this->value = 5;
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }

        // special case for Laravel Collection macro magic
        $fetchedLocalPropertyNameToTypes = $this->localPropertyAnalyzer->resolveFetchedPropertiesToTypesFromClass(
            $node
        );

        $propertiesToComplete = $this->resolvePropertiesToComplete($node, $fetchedLocalPropertyNameToTypes);
        if ($propertiesToComplete === []) {
            return null;
        }

        /** @var string $className */
        $className = $this->getName($node);

        $propertiesToComplete = $this->filterOutExistingProperties($className, $propertiesToComplete);

        $newProperties = $this->missingPropertiesFactory->create(
            $fetchedLocalPropertyNameToTypes,
            $propertiesToComplete
        );

        $node->stmts = array_merge($newProperties, $node->stmts);

        return $node;
    }

    private function shouldSkipClass(Class_ $class): bool
    {
        if ($this->classNodeAnalyzer->isAnonymousClass($class)) {
            return true;
        }

        $className = $this->getName($class);
        if ($className === null) {
            return true;
        }

        // properties are accessed via magic, nothing we can do
        if (method_exists($className, '__set')) {
            return true;
        }

        return method_exists($className, '__get');
    }

    /**
     * @param array<string, Type> $fetchedLocalPropertyNameToTypes
     * @return string[]
     */
    private function resolvePropertiesToComplete(Class_ $class, array $fetchedLocalPropertyNameToTypes): array
    {
        $propertyNames = $this->classLikeAnalyzer->resolvePropertyNames($class);

        /** @var string[] $fetchedLocalPropertyNames */
        $fetchedLocalPropertyNames = array_keys($fetchedLocalPropertyNameToTypes);

        return array_diff($fetchedLocalPropertyNames, $propertyNames);
    }

    /**
     * @param string[] $propertiesToComplete
     * @return string[]
     */
    private function filterOutExistingProperties(string $className, array $propertiesToComplete): array
    {
        $missingPropertyNames = [];

        // remove other properties that are accessible from this scope
        foreach ($propertiesToComplete as $propertyToComplete) {
            /** @var string $propertyToComplete */
            if (property_exists($className, $propertyToComplete)) {
                continue;
            }

            $missingPropertyNames[] = $propertyToComplete;
        }

        return $missingPropertyNames;
    }
}
