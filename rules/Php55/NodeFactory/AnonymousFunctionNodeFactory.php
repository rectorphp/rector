<?php

declare (strict_types=1);
namespace Rector\Php55\NodeFactory;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Parser;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20210514\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class AnonymousFunctionNodeFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/jkLLlM/2
     */
    private const DIM_FETCH_REGEX = '#(\\$|\\\\|\\x0)(?<number>\\d+)#';
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    public function __construct(\RectorPrefix20210514\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \PhpParser\Parser $parser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->parser = $parser;
    }
    public function createAnonymousFunctionFromString(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\Closure
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            // not supported yet
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $phpCode = '<?php ' . $expr->value . ';';
        $contentNodes = (array) $this->parser->parse($phpCode);
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $firstNode = $contentNodes[0] ?? null;
        if (!$firstNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $stmt = $firstNode->expr;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (\PhpParser\Node $node) : Node {
            if (!$node instanceof \PhpParser\Node\Scalar\String_) {
                return $node;
            }
            $match = \RectorPrefix20210514\Nette\Utils\Strings::match($node->value, self::DIM_FETCH_REGEX);
            if (!$match) {
                return $node;
            }
            $matchesVariable = new \PhpParser\Node\Expr\Variable('matches');
            return new \PhpParser\Node\Expr\ArrayDimFetch($matchesVariable, new \PhpParser\Node\Scalar\LNumber((int) $match['number']));
        });
        $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Return_($stmt);
        $anonymousFunction->params[] = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable('matches'));
        return $anonymousFunction;
    }
}
