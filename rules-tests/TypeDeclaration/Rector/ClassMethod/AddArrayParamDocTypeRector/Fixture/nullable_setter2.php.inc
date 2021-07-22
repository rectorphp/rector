<?php
namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class NullableSetter2
{
    /**
     * @var int[]|null
     */
    public ?array $bar;

    public function setBar(?array $bar): void
    {
        $this->bar = $bar;
    }
}
?>
-----
<?php
namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class NullableSetter2
{
    /**
     * @var int[]|null
     */
    public ?array $bar;

    /**
     * @param int[]|null $bar
     */
    public function setBar(?array $bar): void
    {
        $this->bar = $bar;
    }
}
?>
