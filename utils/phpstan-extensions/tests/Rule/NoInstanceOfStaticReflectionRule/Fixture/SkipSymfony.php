<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use Symfony\Component\Console\Helper\ProgressBar;

final class SkipSymfony
{
    public function find($node)
    {
        return $node instanceof ProgressBar;
    }
}
