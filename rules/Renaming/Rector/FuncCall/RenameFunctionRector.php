<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202507\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionRector\RenameFunctionRectorTest
 */
final class RenameFunctionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private array $oldFunctionToNewFunction = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turn defined function call new one', [new ConfiguredCodeSample('view("...", []);', 'Laravel\\Templating\\render("...", []);', ['view' => 'Laravel\\Templating\\render'])]);
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
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return null;
        }
        foreach ($this->oldFunctionToNewFunction as $oldFunction => $newFunction) {
            if (!$this->nodeNameResolver->isStringName($nodeName, $oldFunction)) {
                continue;
            }
            $node->name = $this->createName($newFunction);
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString(\array_values($configuration));
        Assert::allString($configuration);
        $this->oldFunctionToNewFunction = $configuration;
    }
    private function createName(string $newFunction) : Name
    {
        if (\strpos($newFunction, '\\') !== \false) {
            return new FullyQualified($newFunction);
        }
        return new Name($newFunction);
    }
}
