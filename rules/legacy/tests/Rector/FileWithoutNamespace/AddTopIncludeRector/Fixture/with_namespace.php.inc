<?php

namespace Rector\Legacy\Tests\Rector\FileWithoutNamespace\AddTopIncludeRector\Fixture;

if (isset($_POST['csrf'])) {
    processPost($_POST);
}

-----
<?php

namespace Rector\Legacy\Tests\Rector\FileWithoutNamespace\AddTopIncludeRector\Fixture;

require_once __DIR__ . '/../autoloader.php';

if (isset($_POST['csrf'])) {
    processPost($_POST);
}

