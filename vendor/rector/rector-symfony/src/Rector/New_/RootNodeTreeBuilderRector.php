<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/27476
 * @see \Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\RootNodeTreeBuilderRectorTest
 */
final class RootNodeTreeBuilderRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes  Process string argument to an array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->class, new \PHPStan\Type\ObjectType('Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder'))) {
            return null;
        }
        if (isset($node->args[1])) {
            return null;
        }
        $rootMethodCallNode = $this->getRootMethodCallNode($node);
        if (!$rootMethodCallNode instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $firstArg = $rootMethodCallNode->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $rootNameNode = $firstArg->value;
        if (!$rootNameNode instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        [$node->args, $rootMethodCallNode->args] = [$rootMethodCallNode->args, $node->args];
        $rootMethodCallNode->name = new \PhpParser\Node\Identifier('getRootNode');
        return $node;
    }
    private function getRootMethodCallNode(\PhpParser\Node\Expr\New_ $new) : ?\PhpParser\Node
    {
        $expression = $new->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if ($expression === null) {
            return null;
        }
        $nextExpression = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if ($nextExpression === null) {
            return null;
        }
        return $this->betterNodeFinder->findFirst([$nextExpression], function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder'))) {
                return \false;
            }
            if (!$this->isName($node->name, 'root')) {
                return \false;
            }
            return isset($node->args[0]);
        });
    }
}
