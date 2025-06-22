<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeAnalyzer\ParamAnalyzer;
final class UnusedParameterResolver
{
    /**
     * @readonly
     */
    private ParamAnalyzer $paramAnalyzer;
    public function __construct(ParamAnalyzer $paramAnalyzer)
    {
        $this->paramAnalyzer = $paramAnalyzer;
    }
    /**
     * @return array<int, Param>
     */
    public function resolve(ClassMethod $classMethod) : array
    {
        /** @var array<int, Param> $unusedParameters */
        $unusedParameters = [];
        foreach ($classMethod->params as $i => $param) {
            if ($this->paramAnalyzer->isParamUsedInClassMethod($classMethod, $param)) {
                continue;
            }
            $unusedParameters[$i] = $param;
        }
        return $unusedParameters;
    }
}
