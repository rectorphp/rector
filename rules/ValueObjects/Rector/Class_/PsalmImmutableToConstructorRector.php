<?php

declare(strict_types=1);

namespace Rector\ValueObjects\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\RemovingStatic\UniqueObjectFactoryFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\ValueObjects\Rector\Class_\PsalmImmutableToConstructorRector\PsalmImmutableToConstructorRectorTest
 */
final class PsalmImmutableToConstructorRector extends AbstractRector
{
    /**
     * @var UniqueObjectFactoryFactory
     */
    private $uniqueObjectFactoryFactory;

    public function __construct(UniqueObjectFactoryFactory $uniqueObjectFactoryFactory)
    {
        $this->uniqueObjectFactoryFactory = $uniqueObjectFactoryFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Decorate value objects with @psalm-immutable annotations to constructor', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @psalm-immutable
     */
    public string $name;
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @psalm-immutable
     */
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE

            ),
        ]);
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
        $psalmImmutableProperties = $this->matchPsalmImmutableProperties($node);
        if ($psalmImmutableProperties === []) {
            return null;
        }

        // already has constructor → skip
        $existingClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if ($existingClassMethod !== null) {
            return null;
        }

        $constructClassMethod = $this->createConstructClassMethod($psalmImmutableProperties);
        $node->stmts[] = $constructClassMethod;

        return $node;
    }

    /**
     * @return Property[]
     */
    private function matchPsalmImmutableProperties(Class_ $class): array
    {
        $immutableProperties = [];

        foreach ($class->getProperties() as $property) {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if (! $propertyPhpDocInfo->hasByName('psalm-immutable')) {
                continue;
            }

            $immutableProperties[] = $property;
        }

        return $immutableProperties;
    }

    /**
     * @param Property[] $properties
     */
    private function createConstructClassMethod(array $properties): ClassMethod
    {
        $constructClassMethod = $this->nodeFactory->createPublicMethod(MethodName::CONSTRUCT);

        $params = $this->nodeFactory->createParamsFromProperties($properties);
        $constructClassMethod->params = $params;

        $constructClassMethod->stmts = $this->uniqueObjectFactoryFactory->createAssignsFromParams($params);

        return $constructClassMethod;
    }
}
