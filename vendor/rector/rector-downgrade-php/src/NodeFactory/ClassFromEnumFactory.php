<?php

declare (strict_types=1);
namespace Rector\NodeFactory;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\ValueObject\Visibility;
final class ClassFromEnumFactory
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @api downgrade
     */
    public function createFromEnum(Enum_ $enum) : Class_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($enum);
        $classStmts = [];
        foreach ($enum->stmts as $stmt) {
            if (!$stmt instanceof EnumCase) {
                $classStmts[] = $stmt;
                continue;
            }
            $constValue = $this->createConstValue($stmt);
            $classStmts[] = new ClassConst([new Const_($stmt->name, $constValue)], Visibility::PUBLIC, ['startLine' => $stmt->getStartLine(), 'endLine' => $stmt->getEndLine()]);
        }
        $class = new Class_($shortClassName, ['stmts' => $classStmts], ['startLine' => $enum->getStartLine(), 'endLine' => $enum->getEndLine()]);
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
