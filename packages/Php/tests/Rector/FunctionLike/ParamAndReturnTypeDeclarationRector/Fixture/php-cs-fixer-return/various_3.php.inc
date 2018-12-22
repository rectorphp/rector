<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Various3;

/** @return void */ function my_foo() {}
/** @return iterable */ function my_foo2() {}
/** @return object */ function my_foo3() {}
/** @return int */ function my_foo4() {}
/** @return array */ function my_foo5() {}
/** @return callable */ function my_foo6() {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\Various3;

/** @return void */ function my_foo(): void {}
/** @return iterable */ function my_foo2(): iterable {}
/** @return object */ function my_foo3() {}
/** @return int */ function my_foo4(): int {}
/** @return array */ function my_foo5(): array {}
/** @return callable */ function my_foo6(): callable {}

?>
