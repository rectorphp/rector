<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Various;

/** @return Bar */ function my_foo() {}
/** @return My\Bar */ function my_foo2() {}
/** @return \My\Bar */ function my_foo3() {}
interface Foo { /** @return Bar */ function my_foo4(); }
/** @return void */ function my_foo5() {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Various;

/** @return Bar */ function my_foo(): Bar {}
/** @return My\Bar */ function my_foo2(): My\Bar {}
/** @return \My\Bar */ function my_foo3(): \My\Bar {}
interface Foo { /** @return Bar */ function my_foo4(): Bar; }
/** @return void */ function my_foo5(): void {}

?>
