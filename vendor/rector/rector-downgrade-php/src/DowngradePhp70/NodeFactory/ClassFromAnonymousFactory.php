<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\NodeFactory;

use PhpParser\Node\Stmt\Class_;
final class ClassFromAnonymousFactory
{
    public function create(string $className, Class_ $newClass) : Class_
    {
        return new Class_($className, ['flags' => $newClass->flags, 'extends' => $newClass->extends, 'implements' => $newClass->implements, 'stmts' => $newClass->stmts, 'attrGroups' => $newClass->attrGroups]);
    }
}
