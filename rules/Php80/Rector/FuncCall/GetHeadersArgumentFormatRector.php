<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php80\Rector\FuncCall\GetHeadersArgumentFormatRector\GetHeadersArgumentFormatRectorTest
 */
final class GetHeadersArgumentFormatRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change the integer associative argument of the get_headers() function to a boolean', [new CodeSample(<<<'CODE_SAMPLE'
get_headers($url, 1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
get_headers($url, true);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if (!$this->isName($node, 'get_headers')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $formatArg = $node->getArgs()[1] ?? null;
        if (!$formatArg instanceof Arg) {
            return null;
        }
        $format = $this->valueResolver->getValue($formatArg->value);
        if ($format === 0) {
            $formatArg->value = $this->nodeFactory->createFalse();
            return $node;
        }
        if ($format === 1) {
            $formatArg->value = $this->nodeFactory->createTrue();
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_80;
    }
}
