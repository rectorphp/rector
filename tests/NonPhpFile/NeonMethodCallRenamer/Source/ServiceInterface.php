<?php

namespace Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source;

interface ServiceInterface
{
    public function add(string $key, string $value): void;
}
