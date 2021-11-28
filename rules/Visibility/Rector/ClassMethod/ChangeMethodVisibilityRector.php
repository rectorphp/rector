<?php

declare(strict_types=1);

namespace Rector\Visibility\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Visibility;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector\ChangeMethodVisibilityRectorTest
 */
final class ChangeMethodVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_VISIBILITIES = 'method_visibilities';

    /**
     * @var ChangeMethodVisibility[]
     */
    private array $methodVisibilities = [];

    public function __construct(
        private ParentClassScopeResolver $parentClassScopeResolver,
        private VisibilityManipulator $visibilityManipulator,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change visibility of method from parent class.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class FrameworkClass
{
    protected function someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    public function someMethod()
    {
    }
}
CODE_SAMPLE
                                ,
                    <<<'CODE_SAMPLE'
class FrameworkClass
{
    protected function someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    protected function someMethod()
    {
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::METHOD_VISIBILITIES => [
                            new ChangeMethodVisibility('FrameworkClass', 'someMethod', Visibility::PROTECTED),
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
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
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $parentClassName = $this->parentClassScopeResolver->resolveParentClassName($scope);
        if ($parentClassName === null) {
            return null;
        }

        foreach ($this->methodVisibilities as $methodVisibility) {
            if ($methodVisibility->getClass() !== $parentClassName) {
                continue;
            }

            if (! $this->isName($node, $methodVisibility->getMethod())) {
                continue;
            }

            $this->visibilityManipulator->changeNodeVisibility($node, $methodVisibility->getVisibility());

            return $node;
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $methodVisibilities = $configuration[self::METHOD_VISIBILITIES] ?? ($configuration ?: []);

        Assert::isArray($methodVisibilities);
        Assert::allIsInstanceOf($methodVisibilities, ChangeMethodVisibility::class);

        $this->methodVisibilities = $methodVisibilities;
    }
}
