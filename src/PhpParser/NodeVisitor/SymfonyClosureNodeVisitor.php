<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\NodeTraverser\SimpleNodeTraverser;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
final class SymfonyClosureNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * @readonly
     */
    private SymfonyPhpClosureDetector $symfonyPhpClosureDetector;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
    }
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof Closure) {
            return null;
        }
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        SimpleNodeTraverser::decorateWithAttributeValue($node->stmts, AttributeKey::IS_INSIDE_SYMFONY_PHP_CLOSURE, \true);
        return null;
    }
}
