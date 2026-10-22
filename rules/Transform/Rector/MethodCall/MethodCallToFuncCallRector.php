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
use Rector\Transform\ValueObject\MethodCallToFuncCall;
/**
 * @deprecated This rule is deprecated, as changing a method call to a function call is niche and depends on project context. Write a custom rule for the exact project case instead.
 */
final class MethodCallToFuncCallRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change method call to function call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function show()
    {
        return $this->render('some_template');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function show()
    {
        return view('some_template');
    }
}
CODE_SAMPLE
, [new MethodCallToFuncCall('SomeClass', 'render', 'view')])]);
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
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as changing a method call to a function call is niche and depends on project context; write a custom rule for the exact project case instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
