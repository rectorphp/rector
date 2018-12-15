<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Application\FunctionLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;

final class FunctionLikeNodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var FunctionLikeNodeCollector
     */
    private $functionLikeNodeCollector;

    public function __construct(FunctionLikeNodeCollector $functionLikeNodeCollector)
    {
        $this->functionLikeNodeCollector = $functionLikeNodeCollector;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod) {
            $name = (string) $node->getAttribute(Attribute::CLASS_NAME);
            $this->functionLikeNodeCollector->addMethod($node, $name);
        }

        if ($node instanceof Function_) {
            $this->functionLikeNodeCollector->addFunction($node);
        }
    }
}
