<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas;

interface BarInterface
{
    /**
     * @return float
     */
    public function baz(array $a, $b);
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas;

interface BarInterface
{
    /**
     * @return float
     */
    public function baz(array $a, $b): float;
}
?>
