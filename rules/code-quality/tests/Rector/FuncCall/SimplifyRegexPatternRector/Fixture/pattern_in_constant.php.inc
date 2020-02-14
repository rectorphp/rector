<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

class PatternInConstant
{
    const PATTERN = '#[0-9]#';

    public function run($value)
    {
        preg_match(self::PATTERN, $value);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

class PatternInConstant
{
    const PATTERN = '#\d#';

    public function run($value)
    {
        preg_match(self::PATTERN, $value);
    }
}

?>
