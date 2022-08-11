<?php

declare (strict_types=1);
namespace Rector\DowngradePhp54\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NamedVariableFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/instance-method-call
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\DowngradeInstanceMethodCallRectorTest
 */
final class DowngradeInstanceMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NamedVariableFactory
     */
    private $namedVariableFactory;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(NamedVariableFactory $namedVariableFactory, NodesToAddCollector $nodesToAddCollector)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade instance and method call/property access', [new CodeSample(<<<'CODE_SAMPLE'
echo (new \ReflectionClass('\\stdClass'))->getName();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object = new \ReflectionClass('\\stdClass');
echo $object->getName();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, PropertyFetch::class, ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch|MethodCall|PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $variable = $this->namedVariableFactory->createVariable($node, 'object');
        $expression = new Expression(new Assign($variable, $node->var));
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node);
        $node->var = $variable;
        // necessary to remove useless parentheses
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch $node
     */
    private function shouldSkip($node) : bool
    {
        if ($node->var instanceof New_) {
            return \false;
        }
        return !$node->var instanceof Clone_;
    }
}
