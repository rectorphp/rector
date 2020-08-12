<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\StaticCall\RemoveParentCallByParentRector\RemoveParentCallByParentRectorTest
 */
final class RemoveParentCallByParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const PARENT_CLASSES = 'parent_classes';

    /**
     * @var string[]
     */
    private $parentClasses = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove parent call by parent class', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass extends SomeParentClass
{
    public function run()
    {
        parent::someCall();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass extends SomeParentClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
                ,
                [
                    self::PARENT_CLASSES => ['SomeParentClass'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        if (! $this->isName($node->class, 'parent')) {
            return null;
        }

        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        foreach ($this->parentClasses as $parentClass) {
            if ($parentClassName !== $parentClass) {
                continue;
            }

            $this->removeNode($node);
            return null;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->parentClasses = $configuration[self::PARENT_CLASSES] ?? [];
    }
}
