<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

interface RepositoryInterface
{
    /**
     * @param int[] $ids
     * @param int[] $expenseReportIds
     *
     * @return \stdClass[]
     */
    public function listBy(?array $ids = null, ?array $expenseReportIds = null): array;
}

final class SkipArrayIndex
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(object $query): \stdClass
    {
        $objects = $this->repository->listBy([$query->id]);

        if (empty($objects)) {
            throw new \InvalidArgumentException($query->id);
        }

        if (1 !== count($objects)) {
            throw new \UnexpectedValueException();
        }

        return $objects[0];
    }
}
