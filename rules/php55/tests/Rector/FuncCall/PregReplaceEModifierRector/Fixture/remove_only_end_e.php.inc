<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class RemoveOnlyEndE
{
    public function run()
    {
        $comment = preg_replace('~e~e', '"ahoj"', $comment);
    }
}

?>
-----
<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class RemoveOnlyEndE
{
    public function run()
    {
        $comment = preg_replace_callback('~e~', function ($matches) {
            return "ahoj";
        }, $comment);
    }
}

?>
