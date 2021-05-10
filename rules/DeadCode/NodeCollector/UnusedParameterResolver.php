<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
final class UnusedParameterResolver
{
    /**
     * @var \Rector\Core\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\NodeManipulator\ClassMethodManipulator $classMethodManipulator, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return Param[]
     */
    public function resolve(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $unusedParameters = [];
        foreach ($classMethod->params as $i => $param) {
            // skip property promotion
            /** @var Param $param */
            if ($param->flags !== 0) {
                continue;
            }
            if ($this->classMethodManipulator->isParameterUsedInClassMethod($param, $classMethod)) {
                // reset to keep order of removed arguments, if not construtctor - probably autowired
                if (!$this->nodeNameResolver->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
                    $unusedParameters = [];
                }
                continue;
            }
            $unusedParameters[$i] = $param;
        }
        return $unusedParameters;
    }
}
