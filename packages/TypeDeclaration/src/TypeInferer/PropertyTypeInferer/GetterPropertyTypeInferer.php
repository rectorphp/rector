<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeDeclarationToStringConverter;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnedNodesReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTagReturnTypeInferer;

final class GetterPropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var ReturnedNodesReturnTypeInferer
     */
    private $returnedNodesReturnTypeInferer;

    /**
     * @var ReturnTagReturnTypeInferer
     */
    private $returnTagReturnTypeInferer;

    /**
     * @var TypeDeclarationToStringConverter
     */
    private $typeDeclarationToStringConverter;

    public function __construct(
        ReturnedNodesReturnTypeInferer $returnedNodesReturnTypeInferer,
        ReturnTagReturnTypeInferer $returnTagReturnTypeInferer,
        TypeDeclarationToStringConverter $typeDeclarationToStringConverter
    ) {
        $this->returnedNodesReturnTypeInferer = $returnedNodesReturnTypeInferer;
        $this->returnTagReturnTypeInferer = $returnTagReturnTypeInferer;
        $this->typeDeclarationToStringConverter = $typeDeclarationToStringConverter;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        /** @var Class_ $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        /** @var string $propertyName */
        $propertyName = $this->nameResolver->getName($property);

        foreach ($class->getMethods() as $classMethod) {
            if (! $this->hasClassMethodOnlyStatementReturnOfPropertyFetch($classMethod, $propertyName)) {
                continue;
            }

            $returnTypes = $this->inferClassMethodReturnTypes($classMethod);
            if ($returnTypes !== []) {
                return $returnTypes;
            }
        }

        return [];
    }

    public function getPriority(): int
    {
        return 600;
    }

    private function hasClassMethodOnlyStatementReturnOfPropertyFetch(
        ClassMethod $classMethod,
        string $propertyName
    ): bool {
        if (count((array) $classMethod->stmts) !== 1) {
            return false;
        }

        $onlyClassMethodStmt = $classMethod->stmts[0];
        if (! $onlyClassMethodStmt instanceof Return_) {
            return false;
        }

        /** @var Return_ $return */
        $return = $onlyClassMethodStmt;

        if (! $return->expr instanceof PropertyFetch) {
            return false;
        }

        return $this->nameResolver->isName($return->expr, $propertyName);
    }

    /**
     * @return string[]
     */
    private function inferClassMethodReturnTypes(ClassMethod $classMethod): array
    {
        $returnTypeDeclarationTypes = $this->typeDeclarationToStringConverter->resolveFunctionLikeReturnTypeToString(
            $classMethod
        );
        if ($returnTypeDeclarationTypes) {
            return $returnTypeDeclarationTypes;
        }

        $inferedTypes = $this->returnedNodesReturnTypeInferer->inferFunctionLike($classMethod);
        if ($inferedTypes) {
            return $inferedTypes;
        }

        return $this->returnTagReturnTypeInferer->inferFunctionLike($classMethod);
    }
}
