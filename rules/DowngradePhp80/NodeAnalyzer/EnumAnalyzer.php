<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Stmt\Enum_;
use RectorPrefix20220606\PhpParser\Node\Stmt\EnumCase;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\AstResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
final class EnumAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(AstResolver $astResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveType(ClassReflection $classReflection) : ?Identifier
    {
        $class = $this->astResolver->resolveClassFromClassReflection($classReflection, $classReflection->getName());
        if (!$class instanceof Enum_) {
            throw new ShouldNotHappenException();
        }
        $scalarType = $class->scalarType;
        if ($scalarType instanceof Identifier) {
            // can be only int or string
            return $scalarType;
        }
        $enumExprTypes = $this->resolveEnumExprTypes($class);
        $enumExprTypeClasses = [];
        foreach ($enumExprTypes as $enumExprType) {
            $enumExprTypeClasses[] = \get_class($enumExprType);
        }
        $uniqueEnumExprTypeClasses = \array_unique($enumExprTypeClasses);
        if (\count($uniqueEnumExprTypeClasses) === 1) {
            $uniqueEnumExprTypeClass = $uniqueEnumExprTypeClasses[0];
            if (\is_a($uniqueEnumExprTypeClass, StringType::class, \true)) {
                return new Identifier('string');
            }
            if (\is_a($uniqueEnumExprTypeClass, IntegerType::class, \true)) {
                return new Identifier('int');
            }
            if (\is_a($uniqueEnumExprTypeClass, FloatType::class, \true)) {
                return new Identifier('float');
            }
        }
        // unknown or multiple types
        return null;
    }
    /**
     * @return Type[]
     */
    private function resolveEnumExprTypes(Enum_ $enum) : array
    {
        $enumExprTypes = [];
        foreach ($enum->stmts as $classStmt) {
            if (!$classStmt instanceof EnumCase) {
                continue;
            }
            $enumExprTypes[] = $this->resolveEnumCaseType($classStmt);
        }
        return $enumExprTypes;
    }
    private function resolveEnumCaseType(EnumCase $enumCase) : Type
    {
        $classExpr = $enumCase->expr;
        if ($classExpr instanceof Expr) {
            return $this->nodeTypeResolver->getType($classExpr);
        }
        // in case of no value, fallback to string type
        return new StringType();
    }
}
