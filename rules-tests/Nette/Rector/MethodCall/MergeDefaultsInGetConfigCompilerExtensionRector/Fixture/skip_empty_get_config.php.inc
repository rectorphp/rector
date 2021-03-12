<?php

namespace Rector\Tests\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector\Fixture;

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
