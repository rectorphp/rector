<?php

declare (strict_types=1);
namespace Rector\Php81\NodeFactory;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use Rector\Core\ValueObject\Visibility;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassFromEnumFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createFromEnum(\PhpParser\Node\Stmt\Enum_ $enum) : \PhpParser\Node\Stmt\Class_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($enum);
        $classConsts = [];
        foreach ($enum->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\EnumCase) {
                continue;
            }
            $constValue = $this->createConstValue($stmt);
            $classConsts[] = new \PhpParser\Node\Stmt\ClassConst([new \PhpParser\Node\Const_($stmt->name, $constValue)], \Rector\Core\ValueObject\Visibility::PUBLIC);
        }
        $class = new \PhpParser\Node\Stmt\Class_($shortClassName, ['stmts' => $classConsts]);
        $class->namespacedName = $enum->namespacedName;
        return $class;
    }
    private function createConstValue(\PhpParser\Node\Stmt\EnumCase $enumCase) : \PhpParser\Node\Expr
    {
        if ($enumCase->expr instanceof \PhpParser\Node\Expr) {
            return $enumCase->expr;
        }
        /** @var string $enumName */
        $enumName = $this->nodeNameResolver->getName($enumCase);
        // minimal convention
        $lowercasedEnumValue = \strtolower($enumName);
        return new \PhpParser\Node\Scalar\String_($lowercasedEnumValue);
    }
}
