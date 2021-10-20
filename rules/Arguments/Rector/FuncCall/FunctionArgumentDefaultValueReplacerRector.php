<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Arguments\ArgumentDefaultValueReplacer;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @changelog https://php.watch/versions/8.1/version_compare-operator-restrictions
 * @changelog https://github.com/rectorphp/rector/issues/6271
 *
 * @see \Rector\Tests\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector\FunctionArgumentDefaultValueReplacerRectorTest
 */
final class FunctionArgumentDefaultValueReplacerRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REPLACED_ARGUMENTS = 'replaced_arguments';
    /**
     * @var ReplaceFuncCallArgumentDefaultValue[]
     */
    private $replacedArguments = [];
    /**
     * @var \Rector\Arguments\ArgumentDefaultValueReplacer
     */
    private $argumentDefaultValueReplacer;
    public function __construct(\Rector\Arguments\ArgumentDefaultValueReplacer $argumentDefaultValueReplacer)
    {
        $this->argumentDefaultValueReplacer = $argumentDefaultValueReplacer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Streamline the operator arguments of version_compare function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'gte');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'ge');
CODE_SAMPLE
, [self::REPLACED_ARGUMENTS => [new \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'gte', 'ge')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Expr\FuncCall
    {
        foreach ($this->replacedArguments as $replacedArgument) {
            if (!$this->isName($node->name, $replacedArgument->getFunction())) {
                continue;
            }
            $this->argumentDefaultValueReplacer->processReplaces($node, $replacedArgument);
        }
        return $node;
    }
    /**
     * @param array<string, ReplaceFuncCallArgumentDefaultValue[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $replacedArguments = $configuration[self::REPLACED_ARGUMENTS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($replacedArguments, \Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue::class);
        $this->replacedArguments = $replacedArguments;
    }
}
