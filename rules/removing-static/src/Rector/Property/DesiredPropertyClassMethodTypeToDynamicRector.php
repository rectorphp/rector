<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\RemovingStatic\Tests\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector\DesiredPropertyClassMethodTypeToDynamicRectorTest
 */
final class DesiredPropertyClassMethodTypeToDynamicRector extends AbstractRector
{
    /**
     * @var class-string[]
     */
    private $classTypes = [];

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->classTypes = $parameterProvider->provideArrayParameter(Option::TYPES_TO_REMOVE_STATIC_FROM);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change defined static properties and methods to dynamic', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public static $name;

    public static function go()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public $name;

    public function go()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class, ClassMethod::class];
    }

    /**
     * @param Property|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->classTypes as $classType) {
            if (! $this->nodeNameResolver->isInClassNamed($node, $classType)) {
                continue;
            }

            if (! $node->isStatic()) {
                return null;
            }

            $this->visibilityManipulator->makeNonStatic($node);

            return $node;
        }

        return null;
    }
}
