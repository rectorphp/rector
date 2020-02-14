<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Null_;

class A
{
}

/** @return null */
function foo() {
    return null;
}

/** @param null $a */
function foo2($a) {}

/** @param null|A $a */
function foo3($a = null) {}

/** @param null|A $a */
function foo4($a) {}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Null_;

class A
{
}

/** @return null */
function foo() {
    return null;
}

/** @param null $a */
function foo2($a) {}

/** @param null|A $a */
function foo3(?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Null_\A $a = null) {}

/** @param null|A $a */
function foo4(?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Null_\A $a) {}

?>
