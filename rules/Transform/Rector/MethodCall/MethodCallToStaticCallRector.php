<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
/**
 * @deprecated This rule is deprecated, as turning a method call into a static call depends on project context and this generic rule is too niche. Write a custom rule for the exact project case instead.
 */
final class MethodCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change method call to desired static call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return $this->anotherDependency->process('value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return StaticCaller::anotherMethod('value');
    }
}
CODE_SAMPLE
, [new MethodCallToStaticCall('AnotherDependency', 'process', 'StaticCaller', 'anotherMethod')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as turning a method call into a static call depends on project context; write a custom rule for the exact project case instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
