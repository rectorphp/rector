<?php

declare(strict_types=1);

namespace Rector\Core\Stubs;

use Nette\Loaders\RobotLoader;

final class StubLoader
{
    /**
     * @var bool
     */
    private $areStubsLoaded = false;

    /**
     * Load stubs after composer autoload is loaded + rector "process <src>" is loaded,
     * so it is loaded only if the classes are really missing
     */
    public function loadStubs(): void
    {
        if ($this->areStubsLoaded) {
            return;
        }

        // these have to be loaded, as doctrine annotation parser only works with included files
        $stubDirectories = [
            __DIR__ . '/../../stubs/DI/Annotation',
            __DIR__ . '/../../stubs/Doctrine/ORM/Mapping',
            __DIR__ . '/../../stubs/Gedmo/Mapping/Annotation',
            __DIR__ . '/../../stubs/JMS',
            __DIR__ . '/../../stubs/Sensio/Bundle/FrameworkExtraBundle/Configuration',
            __DIR__ . '/../../stubs/Symfony/Bundle/FrameworkExtraBundle/Configuration',
            __DIR__ . '/../../stubs/Symfony/Bridge/Doctrine/Validator/Constraints',
            __DIR__ . '/../../stubs/Symfony/Component/Validator',
            __DIR__ . '/../../stubs/Symfony/Component/Form',
            __DIR__ . '/../../stubs/Symfony/Component/Routing/Annotation',
            __DIR__ . '/../../stubs/Tester',
        ];

        // @see https://github.com/rectorphp/rector/issues/1899
        $stubDirectories = array_filter($stubDirectories, function (string $stubDirectory): bool {
            return file_exists($stubDirectory);
        });

        // stubs might not exists on composer install, to prevent PHPStorm duplicated confusion
        if ($stubDirectories === []) {
            return;
        }

        $robotLoader = new RobotLoader();
        $robotLoader->addDirectory(...$stubDirectories);
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/rector/stubs');
        $robotLoader->register();
        $robotLoader->rebuild();

        $this->areStubsLoaded = true;
    }
}
