<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
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

    public function inferProperty(Property $property): Type
    {
        /** @var Class_|null $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            // anonymous class
            return new MixedType();
        }

        /** @var string $propertyName */
        $propertyName = $this->nameResolver->getName($property);

        foreach ($class->getMethods() as $classMethod) {
            if (! $this->hasClassMethodOnlyStatementReturnOfPropertyFetch($classMethod, $propertyName)) {
                continue;
            }

            $returnType = $this->inferClassMethodReturnType($classMethod);

            if (! $returnType instanceof MixedType) {
                return $returnType;
            }
        }

        return new MixedType();
    }

    public function getPriority(): int
    {
        return 1700;
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

    private function inferClassMethodReturnType(ClassMethod $classMethod): Type
    {
        $returnTypeDeclarationType = $this->typeDeclarationToStringConverter->resolveFunctionLikeReturnTypeToPHPStanType(
            $classMethod
        );

        if (! $returnTypeDeclarationType instanceof MixedType) {
            return $returnTypeDeclarationType;
        }

        $inferedType = $this->returnedNodesReturnTypeInferer->inferFunctionLike($classMethod);
        if (! $inferedType instanceof MixedType) {
            return $inferedType;
        }

        return $this->returnTagReturnTypeInferer->inferFunctionLike($classMethod);
    }
}
