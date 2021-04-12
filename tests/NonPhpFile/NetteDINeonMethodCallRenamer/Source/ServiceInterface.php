<?php

namespace Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source;

interface ServiceInterface
{
    public function add(string $key, string $value): void;
}
