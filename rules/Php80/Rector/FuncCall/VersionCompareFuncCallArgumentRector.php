<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php80\Rector\FuncCall\VersionCompareFuncCallArgumentRector\VersionCompareFuncCallArgumentRectorTest
 */
final class VersionCompareFuncCallArgumentRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var array<string, string>
     */
    private const OPERATOR_MAP = ['gte' => 'ge', 'lte' => 'le', '' => '!=', '!' => '!=', 'g' => 'gt', 'l' => 'lt', 'n' => 'ne'];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Streamline the operator argument of the version_compare() function', [new CodeSample(<<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'gte');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'ge');
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
        if (!$this->isName($node, 'version_compare')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $operatorArg = $node->getArgs()[2] ?? null;
        if (!$operatorArg instanceof Arg) {
            return null;
        }
        $operator = $this->valueResolver->getValue($operatorArg->value);
        if (!is_string($operator)) {
            return null;
        }
        if (!isset(self::OPERATOR_MAP[$operator])) {
            return null;
        }
        $operatorArg->value = new String_(self::OPERATOR_MAP[$operator]);
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_80;
    }
}
