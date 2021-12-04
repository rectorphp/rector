<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class VariableAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    public function isStaticOrGlobal(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (\PhpParser\Node $n) use($variable) : bool {
            if (!$n instanceof \PhpParser\Node\Stmt\Static_ && !$n instanceof \PhpParser\Node\Stmt\Global_) {
                return \false;
            }
            /** @var StaticVar[]|Variable[] $vars */
            $vars = $n->vars;
            foreach ($vars as $var) {
                $staticVarVariable = $var instanceof \PhpParser\Node\Stmt\StaticVar ? $var->var : $var;
                if ($this->nodeComparator->areNodesEqual($staticVarVariable, $variable)) {
                    return \true;
                }
            }
            return \false;
        });
    }
}
