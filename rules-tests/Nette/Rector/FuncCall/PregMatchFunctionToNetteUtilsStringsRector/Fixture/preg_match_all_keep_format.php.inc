<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAllKeepFormat
{
    public function run()
    {
        $mentionPattern = '/(^|[^a-z0-9_])[@]([a-z0-9-_]{3,16})([@\xC0-\xD6\xD8-\xF6\xF8-\xFF]?)/iu';
        $content = 'Hello @tom and @david, how are you?';

        \preg_match_all($mentionPattern, $content, $result);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAllKeepFormat
{
    public function run()
    {
        $mentionPattern = '/(^|[^a-z0-9_])[@]([a-z0-9-_]{3,16})([@\xC0-\xD6\xD8-\xF6\xF8-\xFF]?)/iu';
        $content = 'Hello @tom and @david, how are you?';

        $result = \Nette\Utils\Strings::matchAll($content, $mentionPattern, PREG_SET_ORDER - 1);
    }
}

?>
