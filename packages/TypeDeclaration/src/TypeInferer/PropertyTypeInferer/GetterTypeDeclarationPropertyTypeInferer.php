<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeDeclarationToStringConverter;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class GetterTypeDeclarationPropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var TypeDeclarationToStringConverter
     */
    private $typeDeclarationToStringConverter;

    public function __construct(TypeDeclarationToStringConverter $typeDeclarationToStringConverter)
    {
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

            $returnType = $this->typeDeclarationToStringConverter->resolveFunctionLikeReturnTypeToPHPStanType(
                $classMethod
            );
            // let PhpDoc solve that later for more precise type
            if ($returnType instanceof ArrayType) {
                return new MixedType();
            }

            if (! $returnType instanceof MixedType) {
                return $returnType;
            }
        }

        return new MixedType();
    }

    public function getPriority(): int
    {
        return 630;
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
}
