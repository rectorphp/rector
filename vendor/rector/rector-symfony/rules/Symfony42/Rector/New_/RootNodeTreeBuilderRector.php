<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony42\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/27476
 *
 * @see \Rector\Symfony\Tests\Symfony42\Rector\New_\RootNodeTreeBuilderRector\RootNodeTreeBuilderRectorTest
 */
final class RootNodeTreeBuilderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes TreeBuilder with root() call to constructor passed root and getRootNode() call', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

$treeBuilder = new TreeBuilder();
$rootNode = $treeBuilder->root('acme_root');
$rootNode->someCall();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

$treeBuilder = new TreeBuilder('acme_root');
$rootNode = $treeBuilder->getRootNode();
$rootNode->someCall();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->expr instanceof New_) {
                continue;
            }
            $new = $assign->expr;
            // already has first arg
            if (isset($new->getArgs()[1])) {
                continue;
            }
            if (!$this->isObjectType($new->class, new ObjectType('Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder'))) {
                continue;
            }
            $rootMethodCallNode = $this->getRootMethodCallNode($node);
            if (!$rootMethodCallNode instanceof MethodCall) {
                return null;
            }
            $firstArg = $rootMethodCallNode->getArgs()[0];
            if (!$firstArg->value instanceof String_) {
                return null;
            }
            [$new->args, $rootMethodCallNode->args] = [$rootMethodCallNode->getArgs(), $new->getArgs()];
            $rootMethodCallNode->name = new Identifier('getRootNode');
            return $node;
        }
        return null;
    }
    private function getRootMethodCallNode(StmtsAwareInterface $stmtsAware) : ?Node
    {
        $methodCalls = $this->betterNodeFinder->findInstanceOf($stmtsAware, MethodCall::class);
        foreach ($methodCalls as $methodCall) {
            if (!$this->isName($methodCall->name, 'root')) {
                continue;
            }
            if (!$this->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder'))) {
                continue;
            }
            if (!isset($methodCall->getArgs()[0])) {
                continue;
            }
            return $methodCall;
        }
        return null;
    }
}
