<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

/**
 * @var string $description
 */
class ReturnArrayClosure
{
    public function fields(): array
    {
        return [
            'description' => function() {
                return $this->description;
            },
        ];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

/**
 * @var string $description
 */
class ReturnArrayClosure
{
    /**
     * @return array<string, Closure>
     */
    public function fields(): array
    {
        return [
            'description' => function() {
                return $this->description;
            },
        ];
    }
}

?>
