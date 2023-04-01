<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202304\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FuncCall\FuncCallToNewRector\FuncCallToNewRectorTest
 */
final class FuncCallToNewRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private $functionToNew = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change configured function calls to new Instance', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $array = collection([]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $array = new \Collection([]);
    }
}
CODE_SAMPLE
, ['collection' => ['Collection']])]);
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
        foreach ($this->functionToNew as $function => $new) {
            if (!$this->isName($node, $function)) {
                continue;
            }
            return new New_(new FullyQualified($new), $node->args);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        $this->functionToNew = $configuration;
    }
}
