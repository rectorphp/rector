<?php

declare(strict_types=1);

namespace Rector\Core\Stubs;

final class PHPStanStubLoader
{
    /**
     * @var bool
     */
    private $areStubsLoaded = false;

    /**
     * @var string[]
     */
    private const STUBS = [
        'ReflectionUnionType.php',
        'Attribute.php',
    ];

    /**
     * @var string[]
     */
    private const VENDOR_PATHS = [
        'vendor',
        __DIR__ . '/../../vendor',
        __DIR__ . '/../../../../../vendor',
    ];

    /**
     * @see https://github.com/phpstan/phpstan/issues/4541#issuecomment-779434916
     *
     * Point possible vendor locations by use the __DIR__ as start to locate
     * @see https://github.com/rectorphp/rector/pull/5581 that may not detected in https://getrector.org/ which uses docker to run
     */
    public function loadStubs(): void
    {
        if ($this->areStubsLoaded) {
            return;
        }

        foreach (self::VENDOR_PATHS as $vendorPath) {
            $vendorPath = realpath($vendorPath);
            if (! $vendorPath) {
                continue;
            }

            foreach (self::STUBS as $stub) {
                $path     = sprintf('phar://%s/phpstan/phpstan/phpstan.phar/stubs/runtime/%s', $vendorPath, $stub);
                $isExists = file_exists($path);

                if (! $isExists) {
                    continue 2;
                }

                require_once $path;
            }

            $this->areStubsLoaded = true;

            // already loaded? stop loop
            break;
        }
    }
}
