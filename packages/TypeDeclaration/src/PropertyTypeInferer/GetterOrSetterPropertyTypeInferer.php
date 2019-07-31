<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

final class GetterOrSetterPropertyTypeInferer extends AbstractPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        /** @var Class_ $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        /** @var string $propertyName */
        $propertyName = $this->nameResolver->resolve($property);

        foreach ($class->getMethods() as $classMethod) {
            if (! $this->hasClassMethodOnlyStatementReturnOfPropertyFetch($classMethod, $propertyName)) {
                continue;
            }
            $returnTypes = $this->resolveClassMethodReturnTypes($classMethod);
            if ($returnTypes !== []) {
                return $returnTypes;
            }

            throw new ShouldNotHappenException(sprintf(
                '"%s" for "%s" type',
                __METHOD__,
                $this->betterStandardPrinter->print($classMethod->returnType)
            ));
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
    private function resolveClassMethodReturnTypes(ClassMethod $classMethod): array
    {
        // @todo resolve from doc?
        if (! $classMethod->returnType) {
            return [];
        }

        if ($classMethod->returnType instanceof NullableType) {
            $type = $classMethod->returnType->type;
        } else {
            $type = $classMethod->returnType;
        }

        $result = $this->nameResolver->resolve($type);
        if ($result !== null) {
            $types = [$result];

            if ($classMethod->returnType instanceof NullableType) {
                $types[] = 'null';
            }

            return $types;
        }

        return [];
    }
}
