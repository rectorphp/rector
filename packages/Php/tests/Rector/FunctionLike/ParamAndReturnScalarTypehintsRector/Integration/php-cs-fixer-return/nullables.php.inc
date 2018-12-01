<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerReturn\Nullables;

/** @return null|Bar */ function my_foo() {}
/** @return Bar|null */ function my_foo2() {}
/** @return null|array */ function my_foo3() {}
/** @return null|Foo[] */ function my_foo4() {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerReturn\Nullables;

/** @return null|Bar */ function my_foo(): ?Bar {}
/** @return Bar|null */ function my_foo2(): ?Bar {}
/** @return null|array */ function my_foo3(): ?array {}
/** @return null|Foo[] */ function my_foo4(): ?array {}

?>
