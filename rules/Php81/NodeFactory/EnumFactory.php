<?php

declare (strict_types=1);
namespace Rector\Php81\NodeFactory;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class EnumFactory
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createFromClass(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new \PhpParser\Node\Stmt\Enum_($shortClassName);
        // constant to cases
        foreach ($class->getConstants() as $classConst) {
            $enum->stmts[] = $this->createEnumCase($classConst);
        }
        return $enum;
    }
    private function createEnumCase(\PhpParser\Node\Stmt\ClassConst $classConst) : \PhpParser\Node\Stmt\EnumCase
    {
        $constConst = $classConst->consts[0];
        $enumCase = new \PhpParser\Node\Stmt\EnumCase($constConst->name, $constConst->value);
        // mirrow comments
        $enumCase->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO));
        $enumCase->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS));
        return $enumCase;
    }
}
