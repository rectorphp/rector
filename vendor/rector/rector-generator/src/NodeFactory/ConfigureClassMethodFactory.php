<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\NodeFactory;

use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
final class ConfigureClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\RectorGenerator\NodeFactory\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    public function create(array $ruleConfiguration) : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('configure');
        $classMethod->returnType = new \PhpParser\Node\Identifier('void');
        $configurationVariable = new \PhpParser\Node\Expr\Variable('configuration');
        $configurationParam = new \PhpParser\Node\Param($configurationVariable);
        $configurationParam->type = new \PhpParser\Node\Identifier('array');
        $classMethod->params[] = $configurationParam;
        $assigns = [];
        foreach (\array_keys($ruleConfiguration) as $propertyName) {
            $assign = $this->nodeFactory->createPropertyAssign($propertyName, $configurationVariable);
            $assigns[] = new \PhpParser\Node\Stmt\Expression($assign);
        }
        $classMethod->stmts = $assigns;
        $paramDoc = <<<'CODE_SAMPLE'
/**
 * @param mixed[] $configuration
 */
CODE_SAMPLE;
        $classMethod->setDocComment(new \PhpParser\Comment\Doc($paramDoc));
        return $classMethod;
    }
}
