<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class ClosureNestedType
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

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class ClosureNestedType
{
    private function collectionCallback(): \Closure
    {
        return function (int $offset, int $itemCountPerPage): iterable {
            $stmt = $this->connection->executeQuery('SELECT * FROM *');

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                yield EventsEntity::forCollection();
            }
        };
    }

    private function again(): \Closure
    {
        return function (int $offset, int $itemCountPerPage): iterable {
            return 25;
        };
    }
}

?>