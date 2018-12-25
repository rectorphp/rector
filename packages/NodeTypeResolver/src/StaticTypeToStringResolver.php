<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Collector\CallableCollectorPopulator;

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
            ClosureType::class => ['callable'],
            CallableType::class => ['callable'],
            FloatType::class => ['float'],
            BooleanType::class => ['bool'],
            StringType::class => ['string'],
            NullType::class => ['null'],
            // more complex callables
            function (ArrayType $arrayType): array {
                $types = $this->resolve($arrayType->getItemType());
                foreach ($types as $key => $type) {
                    $types[$key] = $type . '[]';
                }

                return $types;
            },
            function (UnionType $unionType): array {
                $types = [];
                foreach ($unionType->getTypes() as $singleStaticType) {
                    $types = array_merge($types, $this->resolve($singleStaticType));
                }

                return $types;
            },
            function (ObjectType $objectType): array {
                // the must be absolute, since we have no other way to check absolute/local path
                return ['\\' . $objectType->getClassName()];
            },
        ];

        $this->resolversByArgumentType = $callableCollectorPopulator->populate($resolvers);
    }

    /**
     * @return string[]
     */
    public function resolve(?Type $staticType): array
    {
        if ($staticType === null) {
            return [];
        }

        foreach ($this->resolversByArgumentType as $type => $resolverCallable) {
            if (is_a($staticType, $type, true)) {
                return $resolverCallable($staticType);
            }
        }

        return [];
    }
}
