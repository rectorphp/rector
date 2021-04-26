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
        return (function ($a, $b) {
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        })($this->a, $this->b);
    }
}

?>
