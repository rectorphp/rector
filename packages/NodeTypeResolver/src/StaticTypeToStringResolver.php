<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Nette\Utils\Strings;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Collector\CallableCollectorPopulator;

/**
 * @see \Rector\NodeTypeResolver\Tests\StaticTypeToStringResolverTest
 */
final class StaticTypeToStringResolver
{
    /**
     * @var callable[]
     */
    private $resolversByArgumentType = [];

    public function __construct(CallableCollectorPopulator $callableCollectorPopulator)
    {
        $resolvers = [
            IntegerType::class => ['int'],
            ObjectWithoutClassType::class => ['object'],
            ClosureType::class => ['callable'],
            CallableType::class => ['callable'],
            FloatType::class => ['float'],
            BooleanType::class => ['bool'],
            StringType::class => ['string'],
            NullType::class => ['null'],
            MixedType::class => ['mixed'],

            // more complex callables
            function (ArrayType $arrayType): array {
                $types = $this->resolveObjectType($arrayType->getItemType());

                if ($types === []) {
                    return ['array'];
                }

                foreach ($types as $key => $type) {
                    $types[$key] = $type . '[]';
                }

                return array_unique($types);
            },
            function (UnionType $unionType): array {
                $types = [];
                foreach ($unionType->getTypes() as $singleStaticType) {
                    $types = array_merge($types, $this->resolveObjectType($singleStaticType));
                }

                return $types;
            },

            function (IntersectionType $intersectionType): array {
                $types = [];
                foreach ($intersectionType->getTypes() as $singleStaticType) {
                    $types = array_merge($types, $this->resolveObjectType($singleStaticType));
                }

                return $this->removeGenericArrayTypeIfThereIsSpecificArrayType($types);
            },
            function (ObjectType $objectType): array {
                return [$objectType->getClassName()];
            },
        ];

        $this->resolversByArgumentType = $callableCollectorPopulator->populate($resolvers);
    }

    /**
     * @param Type[] $staticTypes
     * @return string[]
     */
    public function resolveTypes(array $staticTypes): array
    {
        $typesAsStrings = [];
        foreach ($staticTypes as $staticType) {
            $currentTypesAsStrings = $this->resolveObjectType($staticType);
            $typesAsStrings = array_merge($typesAsStrings, $currentTypesAsStrings);
        }

        return array_unique($typesAsStrings);
    }

    /**
     * @return string[]
     */
    public function resolveObjectType(?Type $staticType): array
    {
        if ($staticType === null) {
            return [];
        }

        foreach ($this->resolversByArgumentType as $type => $resolverCallable) {
            if (is_a($staticType, $type, true)) {
                $types = $resolverCallable($staticType);

                return array_unique($types);
            }
        }

        return [];
    }

    /**
     * Removes "array" if there is "SomeType[]" already
     *
     * @param string[] $types
     * @return string[]
     */
    private function removeGenericArrayTypeIfThereIsSpecificArrayType(array $types): array
    {
        $hasSpecificArrayType = false;
        foreach ($types as $key => $type) {
            if (Strings::endsWith($type, '[]')) {
                $hasSpecificArrayType = true;
                break;
            }
        }

        if ($hasSpecificArrayType === false) {
            return $types;
        }

        foreach ($types as $key => $type) {
            if ($type === 'array') {
                unset($types[$key]);
            }
        }

        return $types;
    }
}
