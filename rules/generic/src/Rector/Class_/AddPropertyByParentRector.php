<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\ParentDependency;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\AddPropertyByParentRectorTest
 */
final class AddPropertyByParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const PARENT_DEPENDENCIES = 'parent_dependencies';

    /**
     * @var ParentDependency[]
     */
    private $parentDependencies = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add dependency via constructor by parent class type', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass extends SomeParentClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass extends SomeParentClass
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    public function __construct(SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}
CODE_SAMPLE
                ,
                [
                    self::PARENT_DEPENDENCIES => [
                        'SomeParentClass' => ['SomeDependency'],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->extends === null) {
            return null;
        }

        $currentParentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        foreach ($this->parentDependencies as $parentDependency) {
            if ($currentParentClassName !== $parentDependency->getParentClass()) {
                continue;
            }

            $propertyType = new ObjectType($parentDependency->getDependencyType());
            /** @var string $propertyName */
            $propertyName = $this->propertyNaming->getExpectedNameFromType($propertyType);
            $this->addConstructorDependencyToClass($node, $propertyType, $propertyName);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $parentDependencies = $configuration[self::PARENT_DEPENDENCIES] ?? [];
        Assert::allIsInstanceOf($parentDependencies, ParentDependency::class);
        $this->parentDependencies = $parentDependencies;
    }
}
