<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Scans every method and static call across the codebase to swap a bare "GET" string for a class constant - a very wide match for a tiny cosmetic gain, with real risk of false positives on unrelated setMethod()/create()/request() calls.
 */
final class LiteralGetToRequestClassConstantRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace "GET" string by Symfony Request object class constants', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

final class SomeClass
{
    public function detail(FormBuilderInterface $formBuilder)
    {
        $formBuilder->setMethod('GET');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

final class SomeClass
{
    public function detail(FormBuilderInterface $formBuilder)
    {
        $formBuilder->setMethod(\Symfony\Component\HttpFoundation\Request::GET);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it matches a bare "GET" string across every method and static call for a tiny cosmetic gain.', self::class));
    }
}
