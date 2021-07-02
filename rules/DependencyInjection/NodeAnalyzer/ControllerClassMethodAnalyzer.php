<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\NodeAnalyzer;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ControllerClassMethodAnalyzer
{
    public function isInControllerActionMethod(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        /** @var string|null $className */
        $className = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            return \false;
        }
        if (\substr_compare($className, 'Controller', -\strlen('Controller')) !== 0) {
            return \false;
        }
        $classMethod = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        // is probably in controller action
        return $classMethod->isPublic();
    }
}
