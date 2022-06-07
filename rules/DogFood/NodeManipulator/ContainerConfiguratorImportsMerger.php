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
    public function __construct(NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }
    public function merge(Closure $closure) : void
    {
        $setConstantFetches = [];
        $lastImportKey = null;
        foreach ($closure->getStmts() as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $expr = $stmt->expr;
            if (!$expr instanceof MethodCall) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($expr->name, 'import')) {
                continue;
            }
            $importArg = $expr->getArgs();
            $argValue = $importArg[0]->value;
            if (!$argValue instanceof ClassConstFetch) {
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
        $setsMethodCall = new MethodCall(new Variable(self::RECTOR_CONFIG_VARIABLE), 'sets', $args);
        $closure->stmts[$lastImportKey] = new Expression($setsMethodCall);
        \ksort($closure->stmts);
    }
}
