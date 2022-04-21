<?php

declare (strict_types=1);
namespace Rector\DogFood\NodeManipulator;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
final class ContainerConfiguratorImportsMerger
{
    /**
     * @var string
     */
    private const RECTOR_CONFIG_VARIABLE = 'rectorConfig';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }
    public function merge(\PhpParser\Node\Expr\Closure $closure) : void
    {
        $setConstantFetches = [];
        $lastImportKey = null;
        foreach ($closure->getStmts() as $key => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            $expr = $stmt->expr;
            if (!$expr instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($expr->name, 'import')) {
                continue;
            }
            $importArg = $expr->getArgs();
            $argValue = $importArg[0]->value;
            if (!$argValue instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                continue;
            }
            $setConstantFetches[] = $argValue;
            unset($closure->stmts[$key]);
            $lastImportKey = $key;
        }
        if ($setConstantFetches === []) {
            return;
        }
        $args = $this->nodeFactory->createArgs([$setConstantFetches]);
        $setsMethodCall = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::RECTOR_CONFIG_VARIABLE), 'sets', $args);
        $closure->stmts[$lastImportKey] = new \PhpParser\Node\Stmt\Expression($setsMethodCall);
        \ksort($closure->stmts);
    }
}
