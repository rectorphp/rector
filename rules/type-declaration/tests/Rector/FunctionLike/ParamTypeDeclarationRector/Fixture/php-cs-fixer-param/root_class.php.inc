<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\RootClass;

/** @param \My\Bar $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\RootClass;

/** @param \My\Bar $foo */ function my_foo(\My\Bar $foo) {}
?>
