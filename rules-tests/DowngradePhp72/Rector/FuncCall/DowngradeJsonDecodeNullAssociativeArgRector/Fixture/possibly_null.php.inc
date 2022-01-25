<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

class PossiblyNull
{
    function run(string $json, ?bool $associative)
    {
        $value = json_decode($json, $associative);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

class PossiblyNull
{
    function run(string $json, ?bool $associative)
    {
        if ($associative === null) {
            $associative = true;
        }
        $value = json_decode($json, $associative);
    }
}

?>
