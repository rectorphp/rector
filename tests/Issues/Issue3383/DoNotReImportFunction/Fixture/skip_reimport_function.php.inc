<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue3383\DoNotReImportFunction\Fixture;

use function Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\some_view_function;

some_view_function();

?>
