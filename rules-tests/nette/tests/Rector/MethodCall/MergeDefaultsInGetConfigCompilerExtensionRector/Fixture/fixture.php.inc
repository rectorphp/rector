<?php

namespace Rector\Nette\Tests\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector\Fixture;

use Nette\DI\CompilerExtension;

final class SomeExtension extends CompilerExtension
{
    private $defaults = [
        'key' => 'value'
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector\Fixture;

use Nette\DI\CompilerExtension;

final class SomeExtension extends CompilerExtension
{
    private $defaults = [
        'key' => 'value'
    ];

    public function loadConfiguration()
    {
        $config = array_merge($this->defaults, $this->getConfig());
    }
}

?>
