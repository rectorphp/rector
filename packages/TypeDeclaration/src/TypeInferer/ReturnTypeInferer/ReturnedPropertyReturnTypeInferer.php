<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

/**
 * Infer return type from $this->variable → and get type $this->variable from @var annotation
 */
final class ReturnedPropertyReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var PropertyTypeInferer
     */
    private $propertyTypeInferer;

    public function __construct(PropertyTypeInferer $propertyTypeInferer)
    {
        $this->propertyTypeInferer = $propertyTypeInferer;
    }

    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array
    {
        if (! $functionLike instanceof ClassMethod) {
            return [];
        }

        $propertyFetch = $this->matchSingleStmtReturnPropertyFetch($functionLike);
        if ($propertyFetch === null) {
            return [];
        }

        $property = $this->getPropertyByPropertyFetch($propertyFetch);
        if ($property === null) {
            return [];
        }

        return $this->propertyTypeInferer->inferProperty($property);
    }

    private function matchSingleStmtReturnPropertyFetch(ClassMethod $classMethod): ?PropertyFetch
    {
        if (count($classMethod->stmts) !== 1) {
            return null;
        }

        $singleStmt = $classMethod->stmts[0];
        if ($singleStmt instanceof Expression) {
            $singleStmt = $singleStmt->expr;
        }

        // is it return?
        if (! $singleStmt instanceof Return_) {
            return null;
        }

        if (! $singleStmt->expr instanceof PropertyFetch) {
            return null;
        }

        $propertyFetch = $singleStmt->expr;
        if (! $this->nameResolver->isName($propertyFetch->var, 'this')) {
            return null;
        }

        return $propertyFetch;
    }

    private function getPropertyByPropertyFetch(PropertyFetch $propertyFetch): ?Property
    {
        $class = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);
        if ($class instanceof ClassLike) {
            return null;
        }

        $propertyName = $this->nameResolver->getName($propertyFetch->name);
        foreach ($class->stmts as $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }

            if (!$this->nameResolver->isName($stmt, $propertyName)) {
                continue;
            }

            return $stmt;
        }

        return null;
    }
}
