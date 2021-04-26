<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class PropertyFetch
{
    private $a;
    private $b;

    public function run()
    {
        return $this->a <=> $this->b;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class PropertyFetch
{
    private $a;
    private $b;

    public function run()
    {
        return (function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        })($this->a, $this->b);
    }
}

?>
