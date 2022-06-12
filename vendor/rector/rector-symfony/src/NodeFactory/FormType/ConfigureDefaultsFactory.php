<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\FormType;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\ValueObject\Visibility;
final class ConfigureDefaultsFactory
{
    /**
     * @param string[] $paramNames
     */
    public function create(array $paramNames) : ClassMethod
    {
        $configureOptionsClassMethod = new ClassMethod('configureOptions');
        $configureOptionsClassMethod->flags = Visibility::PUBLIC;
        $optionsResolverVariable = new Variable('optionsResolver');
        $configureOptionsClassMethod->params[] = new Param($optionsResolverVariable, null, new FullyQualified('Symfony\\Component\\OptionsResolver\\OptionsResolver'));
        $arrayItems = [];
        foreach ($paramNames as $paramName) {
            $arrayItems[] = new ArrayItem(new ConstFetch(new Name('null')), new String_($paramName));
        }
        $array = new Array_($arrayItems);
        $methodCall = new MethodCall($optionsResolverVariable, 'setDefaults', [new Arg($array)]);
        $configureOptionsClassMethod->stmts[] = new Expression($methodCall);
        return $configureOptionsClassMethod;
    }
}
