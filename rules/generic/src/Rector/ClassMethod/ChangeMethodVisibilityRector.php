<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\ChangeMethodVisibilityRectorTest
 */
final class ChangeMethodVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_TO_VISIBILITY_BY_CLASS = 'method_to_visibility_by_class';

    /**
     * @var string[][] { class => [ method name => visibility ] }
     */
    private $methodToVisibilityByClass = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of method from parent class.',
            [new ConfiguredCodeSample(
<<<'PHP'
class FrameworkClass
{
    protected someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    public someMethod()
    {
    }
}
PHP
                ,
<<<'PHP'
class FrameworkClass
{
    protected someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    protected someMethod()
    {
    }
}
PHP
                ,
                [
                    self::METHOD_TO_VISIBILITY_BY_CLASS => [
                        'FrameworkClass' => [
                            'someMethod' => 'protected',
                        ],
                    ],
                ]
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        // doesn't have a parent class
        if (! $node->hasAttribute(AttributeKey::PARENT_CLASS_NAME)) {
            return null;
        }

        $nodeParentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if (! isset($this->methodToVisibilityByClass[$nodeParentClassName])) {
            return null;
        }
        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        if (! isset($this->methodToVisibilityByClass[$nodeParentClassName][$methodName])) {
            return null;
        }

        $nodeParentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        $visibility = $this->methodToVisibilityByClass[$nodeParentClassName][$methodName];

        $this->changeNodeVisibility($node, $visibility);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->methodToVisibilityByClass = $configuration[self::METHOD_TO_VISIBILITY_BY_CLASS] ?? [];
    }
}
