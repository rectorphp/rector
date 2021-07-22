<?php

declare(strict_types=1);

namespace Rector\Php81\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/readonly_properties_v2
 *
 * @see \Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\ReadOnlyPropertyRectorTest
 */
final class ReadOnlyPropertyRector extends AbstractRector
{
    public function __construct(
        private PropertyManipulator $propertyManipulator
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Decorate read-only property with `readonly` attribute', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private string $name
    ) {
    }

    public function getName()
    {
        return $this->name;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private readonly string $name
    ) {
    }

    public function getName()
    {
        return $this->name;
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
        return [Property::class, Param::class];
    }

    /**
     * @param Property|Param $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Param) {
            return $this->refactorParam($node);
        }

        // 1. is property read-only?
        if ($this->propertyManipulator->isPropertyChangeableExceptConstructor($node)) {
            return null;
        }

        if ($node->isReadonly()) {
            return null;
        }

        $this->visibilityManipulator->makeReadonly($node);
        return $node;
    }

    private function refactorParam(Param $param): Param | null
    {
        if ($param->flags === 0) {
            return null;
        }

        // promoted property?
        if ($this->propertyManipulator->isPropertyChangeableExceptConstructor($param)) {
            return null;
        }

        if ($this->visibilityManipulator->hasVisibility($param, Class_::MODIFIER_READONLY)) {
            return null;
        }

        $this->visibilityManipulator->makeReadonly($param);
        return $param;
    }
}
