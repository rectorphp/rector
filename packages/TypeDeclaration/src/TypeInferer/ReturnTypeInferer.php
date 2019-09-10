<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;

final class ReturnTypeInferer extends AbstractPriorityAwareTypeInferer
{
    /**
     * @var ReturnTypeInfererInterface[]
     */
    private $returnTypeInferers = [];

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param ReturnTypeInfererInterface[] $returnTypeInferers
     */
    public function __construct(TypeFactory $typeFactory, array $returnTypeInferers)
    {
        $this->returnTypeInferers = $this->sortTypeInferersByPriority($returnTypeInferers);
        $this->typeFactory = $typeFactory;
    }

    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        return $this->inferFunctionLikeWithExcludedInferers($functionLike, []);
    }

    /**
     * @param string[] $excludedInferers
     */
    public function inferFunctionLikeWithExcludedInferers(FunctionLike $functionLike, array $excludedInferers): Type
    {
        foreach ($this->returnTypeInferers as $returnTypeInferer) {
            if ($this->shouldSkipExcludedTypeInferer($returnTypeInferer, $excludedInferers)) {
                continue;
            }

            $type = $returnTypeInferer->inferFunctionLike($functionLike);
            $type = $this->normalizeArrayTypeAndArrayNever($type);

            if (! $type instanceof MixedType) {
                return $type;
            }
        }

        return new MixedType();
    }

    /**
     * @param string[] $excludedInferers
     */
    private function shouldSkipExcludedTypeInferer(
        ReturnTypeInfererInterface $returnTypeInferer,
        array $excludedInferers
    ): bool {
        foreach ($excludedInferers as $excludedInferer) {
            $this->ensureIsTypeInferer($excludedInferer);

            if (is_a($returnTypeInferer, $excludedInferer)) {
                return true;
            }
        }

        return false;
    }

    private function ensureIsTypeInferer(string $excludedInferer): void
    {
        if (is_a($excludedInferer, ReturnTypeInfererInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException();
    }

    /**
     * From "string[]|mixed[]" based on empty array to to "string[]"
     */
    private function normalizeArrayTypeAndArrayNever(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        $nonNeverTypes = [];
        foreach ($type->getTypes() as $unionedType) {
            if (! $unionedType instanceof ArrayType) {
                return $type;
            }

            if ($unionedType->getItemType() instanceof NeverType) {
                continue;
            }

            $nonNeverTypes[] = $unionedType;
        }

        return $this->typeFactory->createMixedPassedOrUnionType($nonNeverTypes);
    }
}
