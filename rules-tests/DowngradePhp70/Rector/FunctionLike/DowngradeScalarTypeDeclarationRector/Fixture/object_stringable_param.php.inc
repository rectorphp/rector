<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

final class ObjectStringableParam
{
    public function hasDefinition(string $id)
    {
        return $this->definitions[$id];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

final class ObjectStringableParam
{
    /**
     * @param string $id
     */
    public function hasDefinition($id)
    {
        if (is_object($id)) {
            $id = (string) $id;
        }
        return $this->definitions[$id];
    }
}

?>
