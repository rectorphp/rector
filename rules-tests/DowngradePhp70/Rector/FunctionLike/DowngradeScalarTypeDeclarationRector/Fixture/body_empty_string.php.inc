<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

interface BodyEmptyString
{
    public function hasDefinition(string $id);
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

interface BodyEmptyString
{
    /**
     * @param string $id
     */
    public function hasDefinition($id);
}

?>
