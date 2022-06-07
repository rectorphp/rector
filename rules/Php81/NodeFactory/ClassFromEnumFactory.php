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
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createFromEnum(Enum_ $enum) : Class_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($enum);
        $classConsts = [];
        foreach ($enum->stmts as $stmt) {
            if (!$stmt instanceof EnumCase) {
                continue;
            }
            $constValue = $this->createConstValue($stmt);
            $classConsts[] = new ClassConst([new Const_($stmt->name, $constValue)], Visibility::PUBLIC);
        }
        $class = new Class_($shortClassName, ['stmts' => $classConsts]);
        $class->namespacedName = $enum->namespacedName;
        return $class;
    }
    private function createConstValue(EnumCase $enumCase) : Expr
    {
        if ($enumCase->expr instanceof Expr) {
            return $enumCase->expr;
        }
        /** @var string $enumName */
        $enumName = $this->nodeNameResolver->getName($enumCase);
        // minimal convention
        $lowercasedEnumValue = \strtolower($enumName);
        return new String_($lowercasedEnumValue);
    }
}
