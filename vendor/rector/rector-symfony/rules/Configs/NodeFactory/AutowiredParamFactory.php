<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use Rector\Symfony\Enum\SymfonyAttribute;
final class AutowiredParamFactory
{
    public function create(string $variableName, String_ $string) : Param
    {
        $parameterParam = new Param(new Variable($variableName));
        $parameterParam->attrGroups[] = new AttributeGroup([$this->createAutowireAttribute($string, 'param')]);
        return $parameterParam;
    }
    private function createAutowireAttribute(String_ $string, string $argName) : Attribute
    {
        $args = [new Arg($string, \false, \false, [], new Identifier($argName))];
        return new Attribute(new FullyQualified(SymfonyAttribute::AUTOWIRE), $args);
    }
}
