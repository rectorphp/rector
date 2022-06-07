<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use ReflectionFunction;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.number-format.php#refsect1-function.number-format-changelog
 *
 * @see Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector\DowngradeNumberFormatNoFourthArgRectorTest
 */
final class DowngradeNumberFormatNoFourthArgRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade number_format arg to fill 4th arg when only 3rd arg filled', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return number_format(1000, 2, ',');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return number_format(1000, 2, ',', ',');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $reflectionFunction = new ReflectionFunction('number_format');
        $node->args[3] = new Arg(new String_($reflectionFunction->getParameters()[3]->getDefaultValue()));
        return $node;
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'number_format')) {
            return \true;
        }
        $args = $funcCall->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return \true;
        }
        if (isset($args[3])) {
            return \true;
        }
        return !isset($args[2]);
    }
}
