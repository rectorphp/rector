<?php

declare(strict_types=1);

namespace Rector\CodingStyle\NodeAnalyzer;

use PhpParser\Node\Stmt\Throw_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStan\Type\ShortenedObjectType;

final class ThrowAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function resolveThrownTypes(Throw_ $throw): array
    {
        $thrownType = $this->nodeTypeResolver->getStaticType($throw->expr);

        $class = $this->resolveClassFromType($thrownType);
        if ($class !== null) {
            return [$class];
        }

        if ($thrownType instanceof UnionType) {
            $types = [];
            foreach ($thrownType->getTypes() as $unionedType) {
                $types[] = $this->resolveClassFromType($unionedType);
            }

            return $types;
        }

        if ($thrownType instanceof MixedType) {
            return [];
        }

        throw new NotImplementedYetException(get_class($thrownType));
    }

    private function resolveClassFromType(Type $thrownType): string
    {
        if ($thrownType instanceof ShortenedObjectType) {
            return $thrownType->getFullyQualifiedName();
        }

        if ($thrownType instanceof TypeWithClassName) {
            return $thrownType->getClassName();
        }

        throw new ShouldNotHappenException();
    }
}
