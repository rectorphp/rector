<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

class App
{
    public static function init(): App
    {
        return new App;
    }
}

function () {

    fn() => App::init();

};

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

class App
{
    public static function init(): App
    {
        return new App;
    }
}

function () {

    fn(): \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture\App => App::init();

};

?>
