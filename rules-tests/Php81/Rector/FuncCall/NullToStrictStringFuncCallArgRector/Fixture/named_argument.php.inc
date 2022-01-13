<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class NamedArgument
{
    public function run($subject)
    {
        preg_split(subject: $subject, pattern: '#a#');
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class NamedArgument
{
    public function run($subject)
    {
        preg_split(subject: (string) $subject, pattern: '#a#');
    }
}

?>
