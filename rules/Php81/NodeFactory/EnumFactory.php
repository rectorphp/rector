<?php

declare(strict_types=1);

namespace Rector\Php81\NodeFactory;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class EnumFactory
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function createFromClass(Class_ $class): Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new Enum_($shortClassName);

        // constant to cases
        foreach ($class->getConstants() as $classConst) {
            $enum->stmts[] = $this->createEnumCase($classConst);
        }

        return $enum;
    }

    private function createEnumCase(ClassConst $classConst): EnumCase
    {
        $constConst = $classConst->consts[0];
        $enumCase = new EnumCase($constConst->name, $constConst->value);

        // mirrow comments
        $enumCase->setAttribute(AttributeKey::PHP_DOC_INFO, $classConst->getAttribute(AttributeKey::PHP_DOC_INFO));
        $enumCase->setAttribute(AttributeKey::COMMENTS, $classConst->getAttribute(AttributeKey::COMMENTS));

        return $enumCase;
    }
}
