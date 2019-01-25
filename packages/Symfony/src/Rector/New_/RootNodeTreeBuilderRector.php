<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/symfony/symfony/pull/27476
 */
final class RootNodeTreeBuilderRector extends AbstractRector
{
    /**
     * @var string
     */
    private $treeBuilderClass;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        string $treeBuilderClass = 'Symfony\Component\Config\Definition\Builder\TreeBuilder'
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->treeBuilderClass = $treeBuilderClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes  Process string argument to an array', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

$treeBuilder = new TreeBuilder();
$rootNode = $treeBuilder->root('acme_root');
$rootNode->someCall();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

$treeBuilder = new TreeBuilder('acme_root');
$rootNode = $treeBuilder->getRootNode();
$rootNode->someCall();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node->class, $this->treeBuilderClass)) {
            return null;
        }

        if (isset($node->args[1])) {
            return null;
        }

        /** @var MethodCall|null $rootMethodCallNode */
        $rootMethodCallNode = $this->getRootMethodCallNode($node);
        if ($rootMethodCallNode === null) {
            return null;
        }

        $rootNameNode = $rootMethodCallNode->args[0]->value;
        if (! $rootNameNode instanceof String_) {
            return null;
        }

        // switch arguments
        [$node->args, $rootMethodCallNode->args] = [$rootMethodCallNode->args, $node->args];

        $rootMethodCallNode->name = new Identifier('getRootNode');

        return $node;
    }

    private function getRootMethodCallNode(Node $node): ?Node
    {
        $expression = $node->getAttribute(Attribute::CURRENT_EXPRESSION);
        if ($expression === null) {
            return null;
        }

        $nextExpression = $expression->getAttribute(Attribute::NEXT_NODE);
        if ($nextExpression === null) {
            return null;
        }

        return $this->betterNodeFinder->findFirst([$nextExpression], function (Node $node) {
            if (! $node instanceof MethodCall) {
                return false;
            }

            if (! $this->isType($node, $this->treeBuilderClass)) {
                return false;
            }

            if (! $this->isName($node, 'root')) {
                return false;
            }
            return isset($node->args[0]);
        });
    }
}
