<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class SkipGeneric
{
    private readonly UseCaseBusInterface $useCaseBus;

    /**
     * @template T
     *
     * @param UseCaseInterface<T> $useCase
     *
     * @return T
     */
    protected function execute(UseCaseInterface $useCase): mixed
    {
        return $this->useCaseBus->execute($useCase);
    }
}
