<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueDowngradeCatchThrowable\Fixture;

use Throwable;
use Exception;
try {
    // Some code...
} catch (Throwable $exception) {
    handle();
} catch (Exception $exception) {
    handle();
}

?>
