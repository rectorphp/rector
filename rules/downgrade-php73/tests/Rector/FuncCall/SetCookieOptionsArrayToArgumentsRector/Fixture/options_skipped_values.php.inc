<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\Fixture;

class OptionsSkippedValues {
    public function setCookies()
    {
        setcookie('name', 'value', ['path' => 'path']);
        setcookie('name', 'value', ['domain' => 'domain']);
        setcookie('name', 'value', ['secure' => true]);
        setcookie('name', 'value', ['httponly' => true]);
    }
}
?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\Fixture;

class OptionsSkippedValues {
    public function setCookies()
    {
        setcookie('name', 'value', 0, 'path');
        setcookie('name', 'value', 0, '', 'domain');
        setcookie('name', 'value', 0, '', '', true);
        setcookie('name', 'value', 0, '', '', false, true);
    }
}
?>
