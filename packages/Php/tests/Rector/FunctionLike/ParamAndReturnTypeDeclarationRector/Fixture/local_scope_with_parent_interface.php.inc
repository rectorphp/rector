<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Fixture;

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

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Fixture;

interface SniffInterface
{
    /**
     * @param int $position
     */
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
