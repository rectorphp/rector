<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use Rector\NodeAnalyzer\CallLikeArgumentNameAdder;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\CallLike\AddNameToNullArgumentRector\AddNameToNullArgumentRectorTest
 */
final class AddNameToNullArgumentRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private CallLikeArgumentNameAdder $callLikeArgumentNameAdder;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(CallLikeArgumentNameAdder $callLikeArgumentNameAdder, ValueResolver $valueResolver)
    {
        $this->callLikeArgumentNameAdder = $callLikeArgumentNameAdder;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add parameter names to null arguments.', [new CodeSample(<<<'CODE_SAMPLE'
some_function($value, null);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
some_function($value, default: null);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [CallLike::class];
    }
    /**
     * @param CallLike $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->callLikeArgumentNameAdder->addNamesToArgs($node, fn($expr): bool => $this->valueResolver->isNull($expr));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NAMED_ARGUMENTS;
    }
}
