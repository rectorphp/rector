<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\DummyCollection;

final class MyLibrary
{
    private DummyCollection $books;

    public function __construct() {
        $this->books = new DummyCollection();
    }

    public function run(): void
    {

        foreach ($this->getBooks() as $item) {
            var_dump($item);
        }
    }

    public function getBooks(): DummyCollection
    {
        return $this->books;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source\DummyCollection;

final class MyLibrary
{
    private DummyCollection $books;

    public function __construct() {
        $this->books = new DummyCollection();
    }

    public function run(): void
    {

        foreach ($this->getBooks() as $book) {
            var_dump($book);
        }
    }

    public function getBooks(): DummyCollection
    {
        return $this->books;
    }
}

?>
