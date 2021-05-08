<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

final class MultipleObjectStringableParam
{
    public function hasDefinition(string $id, string $name)
    {
        return $this->definitions[$id] . $name;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

final class MultipleObjectStringableParam
{
    /**
     * @param string $id
     * @param string $name
     */
    public function hasDefinition($id, $name)
    {
        $id = (string) $id;
        $name = (string) $name;
        return $this->definitions[$id] . $name;
    }
}

?>
