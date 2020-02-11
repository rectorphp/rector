<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Iterable;

/** @param iterable $counter */ function my_foo($counter) {}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Iterable;

/** @param iterable $counter */ function my_foo(iterable $counter) {}
?>
