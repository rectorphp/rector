<?php

namespace Rector\Nette\Tests\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector\Fixture;

use Nette\DI\CompilerExtension;

final class SkipEmptyGetConfig extends CompilerExtension
{
    private $defaults = [
        'key' => 'value'
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig();
    }
}
