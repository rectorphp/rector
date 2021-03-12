<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyStrposLowerRector\Fixture;

final class Strpos
{
    public function run()
    {
        $string = 'hey';
        strpos(strtolower($string), 'find-me');

        $funcName = 'strpos';
        $funcName(strtolower($string), 'find-me');
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyStrposLowerRector\Fixture;

final class Strpos
{
    public function run()
    {
        $string = 'hey';
        stripos($string, 'find-me');

        $funcName = 'strpos';
        $funcName(strtolower($string), 'find-me');
    }
}

?>
