<?php

declare(strict_types=1);

namespace Rector\Php55\NodeFactory;

use Nette\Utils\Strings;
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
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class AnonymousFunctionNodeFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/jkLLlM/2
     */
    private const DIM_FETCH_REGEX = '#(\\$|\\\\|\\x0)(?<number>\d+)#';

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, Parser $parser)
    {
        $this->parser = $parser;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    public function createAnonymousFunctionFromString(Expr $expr): ?Closure
    {
        if (! $expr instanceof String_) {
            // not supported yet
            throw new ShouldNotHappenException();
        }

        $phpCode = '<?php ' . $expr->value . ';';
        $contentNodes = (array) $this->parser->parse($phpCode);

        $anonymousFunction = new Closure();

        $firstNode = $contentNodes[0] ?? null;
        if (! $firstNode instanceof Expression) {
            return null;
        }

        $stmt = $firstNode->expr;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (Node $node): Node {
            if (! $node instanceof String_) {
                return $node;
            }

            $match = Strings::match($node->value, self::DIM_FETCH_REGEX);
            if (! $match) {
                return $node;
            }

            $matchesVariable = new Variable('matches');

            return new ArrayDimFetch($matchesVariable, new LNumber((int) $match['number']));
        });

        $anonymousFunction->stmts[] = new Return_($stmt);
        $anonymousFunction->params[] = new Param(new Variable('matches'));

        return $anonymousFunction;
    }
}
