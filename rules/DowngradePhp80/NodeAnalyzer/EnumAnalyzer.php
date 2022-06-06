<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
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
    public function __construct(\Rector\Core\PhpParser\AstResolver $astResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveType(\PHPStan\Reflection\ClassReflection $classReflection) : ?\PhpParser\Node\Identifier
    {
        $class = $this->astResolver->resolveClassFromClassReflection($classReflection, $classReflection->getName());
        if (!$class instanceof \PhpParser\Node\Stmt\Enum_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $scalarType = $class->scalarType;
        if ($scalarType instanceof \PhpParser\Node\Identifier) {
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
            if (\is_a($uniqueEnumExprTypeClass, \PHPStan\Type\StringType::class, \true)) {
                return new \PhpParser\Node\Identifier('string');
            }
            if (\is_a($uniqueEnumExprTypeClass, \PHPStan\Type\IntegerType::class, \true)) {
                return new \PhpParser\Node\Identifier('int');
            }
            if (\is_a($uniqueEnumExprTypeClass, \PHPStan\Type\FloatType::class, \true)) {
                return new \PhpParser\Node\Identifier('float');
            }
        }
        // unknown or multiple types
        return null;
    }
    /**
     * @return Type[]
     */
    private function resolveEnumExprTypes(\PhpParser\Node\Stmt\Enum_ $enum) : array
    {
        $enumExprTypes = [];
        foreach ($enum->stmts as $classStmt) {
            if (!$classStmt instanceof \PhpParser\Node\Stmt\EnumCase) {
                continue;
            }
            $enumExprTypes[] = $this->resolveEnumCaseType($classStmt);
        }
        return $enumExprTypes;
    }
    private function resolveEnumCaseType(\PhpParser\Node\Stmt\EnumCase $enumCase) : \PHPStan\Type\Type
    {
        $classExpr = $enumCase->expr;
        if ($classExpr instanceof \PhpParser\Node\Expr) {
            return $this->nodeTypeResolver->getType($classExpr);
        }
        // in case of no value, fallback to string type
        return new \PHPStan\Type\StringType();
    }
}
