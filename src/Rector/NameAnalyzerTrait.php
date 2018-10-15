<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeAnalyzer\CallAnalyzer;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NameAnalyzerTrait
{
    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    /**
     * @required
     */
    public function setCallAnalyzer(CallAnalyzer $callAnalyzer): void
    {
        $this->callAnalyzer = $callAnalyzer;
    }

    public function isName(Node $node, string $name): bool
    {
        return $this->getName($node) === $name;
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        return in_array($this->getName($node), $names, true);
    }

    public function getName(Node $node): ?string
    {
        if ($node instanceof Variable) {
            // be careful, can be expression!
            return (string) $node->name;
        }

        if ($node instanceof MethodCall || $node instanceof StaticCall || $node instanceof FuncCall) {
            return $this->callAnalyzer->resolveName($node);
        }

        if ($node instanceof ClassMethod || $node instanceof ClassConstFetch || $node instanceof PropertyFetch) {
            return (string) $node->name;
        }

        return null;
    }
}
