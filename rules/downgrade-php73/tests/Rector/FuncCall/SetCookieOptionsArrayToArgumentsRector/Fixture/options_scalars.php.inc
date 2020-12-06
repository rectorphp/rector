<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\Fixture;

class OptionsScalars {
    public function setCookies()
    {
        setcookie('name', 'value', ['expires' => 3600]);
        setcookie('name', 'value', ['expires' => 1, 'path' => 'path']);
        setcookie('name', 'value', ['expires' => 1, 'path' => 'path', 'domain' => 'domain']);
        setcookie('name', 'value', ['expires' => 1, 'path' => 'path', 'domain' => 'domain', 'secure' => true]);
        setcookie('name', 'value', ['expires' => 1, 'path' => 'path', 'domain' => 'domain', 'secure' => true, 'httponly' => true]);
        setrawcookie('name', 'value', ['expires' => 3600]);
        setrawcookie('name', 'value', ['expires' => 3600, 'ignored' => 5000]);
    }
}
?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\Fixture;

class OptionsScalars {
    public function setCookies()
    {
        setcookie('name', 'value', 3600);
        setcookie('name', 'value', 1, 'path');
        setcookie('name', 'value', 1, 'path', 'domain');
        setcookie('name', 'value', 1, 'path', 'domain', true);
        setcookie('name', 'value', 1, 'path', 'domain', true, true);
        setrawcookie('name', 'value', 3600);
        setrawcookie('name', 'value', 3600);
    }
}
?>
