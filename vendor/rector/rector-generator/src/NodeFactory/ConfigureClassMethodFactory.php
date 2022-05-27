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
    public function create(array $ruleConfiguration) : ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('configure');
        $classMethod->returnType = new Identifier('void');
        $configurationVariable = new Variable('configuration');
        $configurationParam = new Param($configurationVariable);
        $configurationParam->type = new Identifier('array');
        $classMethod->params[] = $configurationParam;
        $assigns = [];
        foreach (\array_keys($ruleConfiguration) as $propertyName) {
            $assign = $this->nodeFactory->createPropertyAssign($propertyName, $configurationVariable);
            $assigns[] = new Expression($assign);
        }
        $classMethod->stmts = $assigns;
        $paramDoc = <<<'CODE_SAMPLE'
/**
 * @param mixed[] $configuration
 */
CODE_SAMPLE;
        $classMethod->setDocComment(new Doc($paramDoc));
        return $classMethod;
    }
}
