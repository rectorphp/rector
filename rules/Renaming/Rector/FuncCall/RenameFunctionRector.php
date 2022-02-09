<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionRector\RenameFunctionRectorTest
 */
final class RenameFunctionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const OLD_FUNCTION_TO_NEW_FUNCTION = 'old_function_to_new_function';
    /**
     * @var array<string, string>
     */
    private $oldFunctionToNewFunction = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns defined function call new one.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample('view("...", []);', 'Laravel\\Templating\\render("...", []);', ['view' => 'Laravel\\Templating\\render'])]);
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
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->oldFunctionToNewFunction as $oldFunction => $newFunction) {
            if (!$this->isName($node, $oldFunction)) {
                continue;
            }
            // not to refactor here
            $isVirtual = (bool) $node->name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::VIRTUAL_NODE, \false);
            if ($isVirtual) {
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
        $oldFunctionToNewFunction = $configuration[self::OLD_FUNCTION_TO_NEW_FUNCTION] ?? $configuration;
        \RectorPrefix20220209\Webmozart\Assert\Assert::isArray($oldFunctionToNewFunction);
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString(\array_values($oldFunctionToNewFunction));
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString($oldFunctionToNewFunction);
        $this->oldFunctionToNewFunction = $oldFunctionToNewFunction;
    }
    private function createName(string $newFunction) : \PhpParser\Node\Name
    {
        if (\strpos($newFunction, '\\') !== \false) {
            return new \PhpParser\Node\Name\FullyQualified($newFunction);
        }
        return new \PhpParser\Node\Name($newFunction);
    }
}
