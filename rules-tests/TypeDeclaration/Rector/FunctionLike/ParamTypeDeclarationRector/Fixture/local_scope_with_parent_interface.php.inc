<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

interface SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position);
}

final class CoolSniffWithLocalinterface implements SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position)
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

interface SniffInterface
{
    public function process(string $file, int $position);
}

final class CoolSniffWithLocalinterface implements SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, int $position)
    {
    }
}

?>
