<?php

declare (strict_types=1);
namespace Rector\Core\Stubs;

final class PHPStanStubLoader
{
    /**
     * @var string[]
     */
    private const STUBS = ['ReflectionUnionType.php', 'Attribute.php'];
    /**
     * @var string[]
     */
    private const VENDOR_PATHS = [
        // 1. relative path with composer require rector/rector and run vendor/bin/rector
        'vendor',
        // 2. relative path with composer require rector/rector with symlink run vendor/bin/rector
        __DIR__ . '/../../vendor',
        // 3. run outside project like in https://getrector.org/ from docker, so it look up // vendor/rector/rector/bin/rector
        __DIR__ . '/../../../../../vendor',
    ];
    /**
     * @var bool
     */
    private $areStubsLoaded = \false;
    /**
     * @see https://github.com/phpstan/phpstan/issues/4541#issuecomment-779434916
     *
     * Point possible vendor locations by use the __DIR__ as start to locate
     * @see https://github.com/rectorphp/rector/pull/5581 that may not detected in https://getrector.org/ which uses docker to run
     */
    public function loadStubs() : void
    {
        if ($this->areStubsLoaded) {
            return;
        }
        foreach (self::VENDOR_PATHS as $vendorPath) {
            $vendorPath = \realpath($vendorPath);
            if (!$vendorPath) {
                continue;
            }
            foreach (self::STUBS as $stub) {
                $path = $this->getStubPath($vendorPath, $stub);
                if ($path === null) {
                    continue 2;
                }
                require_once $path;
            }
            $this->areStubsLoaded = \true;
            // already loaded? stop loop
            break;
        }
    }
    private function getStubPath(string $vendorPath, string $stub) : ?string
    {
        // @todo: need to be switched when phpstan-extracted used after scoped php 7.0 works
        $path = \sprintf('phar://%s/phpstan/phpstan/phpstan.phar/stubs/runtime/%s', $vendorPath, $stub);
        $isExists = \file_exists($path);
        if (!$isExists) {
            // this is to handle phpstan's stubs got from phpstan-extracted instead of the .phar when exists after scoped php 7.0 applied
            $path = \sprintf('%s/phpstan/phpstan-extracted/stubs/runtime/%s', $vendorPath, $stub);
            $isExists = \file_exists($path);
        }
        if ($isExists) {
            return $path;
        }
        return null;
    }
}
