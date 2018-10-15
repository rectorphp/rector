<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
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
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            return $this->isName($node, $name);
        }

        if ($node instanceof ClassMethod) {
            return (string) $node->name === $name;
        }

        if ($node instanceof FuncCall) {
            return $this->callAnalyzer->isName($node, $name);
        }

        return false;
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            return $this->callAnalyzer->isNames($node, $names);
        }

        if ($node instanceof ClassMethod) {
            return in_array((string) $node->name, $names, true);
        }

        if ($node instanceof FuncCall) {
            return $this->callAnalyzer->isNames($node, $names);
        }

        return false;
    }
}
