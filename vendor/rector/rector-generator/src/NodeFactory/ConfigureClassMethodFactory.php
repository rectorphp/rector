<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\RectorGenerator\NodeFactory;

use RectorPrefix20220606\PhpParser\Comment\Doc;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
final class ConfigureClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
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
