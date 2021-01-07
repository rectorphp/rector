<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4191\DoNotDuplicateComment\Fixture;

// first comment
// second comment
// third comment
class b {}

/**
 */
// forth comment
// fifth comment
// sixth comment
class c {}

?>
