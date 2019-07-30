<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

final class GetterOrSetterPropertyTypeInferer extends AbstractPropertyTypeInferer implements PropertyTypeInfererInterface
{
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
            if (count((array) $classMethod->stmts) !== 1) {
                continue;
            }

            $onlyClassMethodStmt = $classMethod->stmts[0];
            if (! $onlyClassMethodStmt instanceof Return_) {
                continue;
            }

            if (! $onlyClassMethodStmt->expr instanceof PropertyFetch) {
                continue;
            }

            if (! $this->nameResolver->isName($onlyClassMethodStmt->expr, $propertyName)) {
                continue;
            }

            // @todo resolve from doc?
            if (! $classMethod->returnType) {
                continue;
            }

            $result = $this->nameResolver->resolve($classMethod->returnType);
            if ($result !== null) {
                return [$result];
            }

            throw new ShouldNotHappenException(__METHOD__);
        }

        return [];
    }
}
