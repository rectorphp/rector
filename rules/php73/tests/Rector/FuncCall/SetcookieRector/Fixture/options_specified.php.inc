<?php

namespace Rector\Php73\Tests\Rector\FuncCall\SetcookieRector\Fixture;

class OptionsSpecified
{
    public function run()
    {
        $expire = 3600;
        $path = '';
        $domain = '';
        $secure = false;
        $httponly = false;
        setcookie('name', 'value', 3600);
        setcookie('name', 'value', $expire, $path);
        setcookie('name', 'value', $expire, $path, $domain);
        setcookie('name', 'value', $expire, $path, $domain, $secure);
        setcookie('name', 'value', $expire, $path, $domain, $secure, $httponly);
        setcookie('name', 'value', 0, '');
        setcookie('name', 'value', 0, '', '');
        setcookie('name', 'value', 0, '', '', false);
        setcookie('name', 'value', 0, '', '', false, false);
        setrawcookie('name', 'value', 3600);
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\SetcookieRector\Fixture;

class OptionsSpecified
{
    public function run()
    {
        $expire = 3600;
        $path = '';
        $domain = '';
        $secure = false;
        $httponly = false;
        setcookie('name', 'value', ['expires' => 3600]);
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path]);
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path, 'domain' => $domain]);
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path, 'domain' => $domain, 'secure' => $secure]);
        setcookie('name', 'value', ['expires' => $expire, 'path' => $path, 'domain' => $domain, 'secure' => $secure, 'httponly' => $httponly]);
        setcookie('name', 'value', ['expires' => 0, 'path' => '']);
        setcookie('name', 'value', ['expires' => 0, 'path' => '', 'domain' => '']);
        setcookie('name', 'value', ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => false]);
        setcookie('name', 'value', ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => false, 'httponly' => false]);
        setrawcookie('name', 'value', ['expires' => 3600]);
    }
}

?>
