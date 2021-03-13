<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

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

namespace Rector\Tests\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

class PatternInConstant
{
    const PATTERN = '#\d#';

    public function run($value)
    {
        preg_match(self::PATTERN, $value);
    }
}

?>
