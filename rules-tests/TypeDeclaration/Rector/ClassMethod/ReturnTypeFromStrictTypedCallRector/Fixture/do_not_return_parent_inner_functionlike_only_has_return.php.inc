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

    function () {
        return App::init();
    };

    function foo() {
        return App::init();
    };

    new class {
        public function run()
        {
            return App::init();
        }
    };

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

    function (): \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture\App {
        return App::init();
    };

    function foo(): \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture\App {
        return App::init();
    };

    new class {
        public function run(): \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture\App
        {
            return App::init();
        }
    };

};

?>
