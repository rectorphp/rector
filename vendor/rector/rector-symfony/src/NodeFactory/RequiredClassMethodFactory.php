<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use RectorPrefix202301\Nette\Utils\Strings;
use PhpParser\Builder\Method;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use RectorPrefix202301\Webmozart\Assert\Assert;
final class RequiredClassMethodFactory
{
    /**
     * @param string[] $classNames
     */
    public function createRequiredAutowireClassMethod(array $classNames) : ClassMethod
    {
        Assert::allString($classNames);
        $method = new Method('autowire');
        $method->makePublic();
        foreach ($classNames as $className) {
            $variableName = $this->resolveVariableNameFromClassName($className);
            $param = new Param(new Variable($variableName));
            $param->type = new FullyQualified($className);
            $method->addParam($param);
            $assign = $this->createAssign($variableName);
            $method->addStmt(new Expression($assign));
        }
        $autowireClassMethod = $method->getNode();
        $autowireClassMethod->setDocComment(new Doc('/**' . \PHP_EOL . ' * @required' . \PHP_EOL . ' */'));
        return $autowireClassMethod;
    }
    private function resolveVariableNameFromClassName(string $className) : string
    {
        $shortClassName = Strings::after($className, '\\', -1);
        if (!\is_string($shortClassName)) {
            $shortClassName = $className;
        }
        return \lcfirst($shortClassName);
    }
    private function createAssign(string $variableName) : Assign
    {
        $thisVariable = new Variable('this');
        return new Assign(new PropertyFetch($thisVariable, $variableName), new Variable($variableName));
    }
}
