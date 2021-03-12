<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class SkipAnonymousFunctionNestedType
{
    private function collectionCallback(): callable
    {
        return function (int $offset, int $itemCountPerPage): iterable {
            $stmt = $this->connection->executeQuery('SELECT * FROM *');

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                yield EventsEntity::forCollection();
            }
        };
    }

    private function again(): callable
    {
        return function (int $offset, int $itemCountPerPage): iterable {
            return 25;
        };
    }
}
