<?php
namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class NullableSetter
{
    /**
     * @var int[]
     */
    public array $bar;

    public function setBar(?array $bar): void
    {
        if ($bar === null) {
            $bar = [];
        }

        $this->bar = $bar;
    }
}
?>
-----
<?php
namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class NullableSetter
{
    /**
     * @var int[]
     */
    public array $bar;

    /**
     * @param int[]|null $bar
     */
    public function setBar(?array $bar): void
    {
        if ($bar === null) {
            $bar = [];
        }

        $this->bar = $bar;
    }
}
?>
