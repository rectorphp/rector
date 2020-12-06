<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\Fixture;

class OptionsVariables {
    public function setCookies()
    {
        $expire = 3600;
        $path = '';
        $domain = '';
        $secure = false;
        $httponly = false;
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path]);
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path, 'domain' => $domain]);
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path, 'domain' => $domain, 'secure' => $secure]);
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path, 'domain' => $domain, 'secure' => $secure, 'httponly' => $httponly]);
    }
}
?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\Fixture;

class OptionsVariables {
    public function setCookies()
    {
        $expire = 3600;
        $path = '';
        $domain = '';
        $secure = false;
        $httponly = false;
        setcookie('name', 'value', $expire, $path);
        setcookie('name', 'value', $expire, $path, $domain);
        setcookie('name', 'value', $expire, $path, $domain, $secure);
        setcookie('name', 'value', $expire, $path, $domain, $secure, $httponly);
    }
}
?>
