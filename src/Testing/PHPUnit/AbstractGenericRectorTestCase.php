<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Set\SetProvider;
use Symfony\Component\HttpKernel\KernelInterface;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractGenericRectorTestCase extends AbstractKernelTestCase
{
    /**
     * @var mixed[]
     */
    private $oldParameterValues = [];

    protected function setUp(): void
    {
        $this->oldParameterValues = [];
    }

    protected function tearDown(): void
    {
        $this->restoreOldParameterValues();
    }

    protected function getRectorClass(): string
    {
        // can be implemented
        return '';
    }

    protected function provideConfigFileInfo(): ?SmartFileInfo
    {
        if ($this->provideSet() !== '') {
            $setProvider = new SetProvider();
            $set = $setProvider->provideFilePathByName($this->provideSet());
            if ($set === null) {
                $message = sprintf('Invalid set name provided "%s"', $this->provideSet());
                throw new ShouldNotHappenException($message);
            }

            return $set->getFileInfo();
        }

        // can be implemented
        return null;
    }

    protected function provideSet(): string
    {
        // can be implemented
        return '';
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        // can be implemented, has the highest priority
        return [];
    }

    /**
     * @return mixed[]|null[]
     */
    protected function getCurrentTestRectorClassesWithConfiguration(): array
    {
        if ($this->getRectorsWithConfiguration() !== []) {
            foreach (array_keys($this->getRectorsWithConfiguration()) as $rectorClass) {
                $this->ensureRectorClassIsValid($rectorClass, 'getRectorsWithConfiguration');
            }

            return $this->getRectorsWithConfiguration();
        }

        $rectorClass = $this->getRectorClass();
        $this->ensureRectorClassIsValid($rectorClass, 'getRectorClass');

        return [$rectorClass => null];
    }

    /**
     * Return interface type that extends @see \Rector\Core\Contract\Rector\RectorInterface;
     */
    abstract protected function getRectorInterface(): string;

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }

    protected function setParameter(string $name, $value): void
    {
        $parameterProvider = self::$container->get(ParameterProvider::class);

        if ($name !== Option::PHP_VERSION_FEATURES) {
            $oldParameterValue = $parameterProvider->provideParameter($name);
            $this->oldParameterValues[$name] = $oldParameterValue;
        }

        $parameterProvider->changeParameter($name, $value);
    }

    /**
     * @param SmartFileInfo[] $configFileInfos
     */
    protected function bootKernelWithConfigInfos(string $class, array $configFileInfos): KernelInterface
    {
        $configFiles = [];
        foreach ($configFileInfos as $configFileInfo) {
            $configFiles[] = $configFileInfo->getRealPath();
        }

        return $this->bootKernelWithConfigs($class, $configFiles);
    }

    private function restoreOldParameterValues(): void
    {
        if ($this->oldParameterValues === []) {
            return;
        }

        $parameterProvider = self::$container->get(ParameterProvider::class);

        foreach ($this->oldParameterValues as $name => $oldParameterValue) {
            $parameterProvider->changeParameter($name, $oldParameterValue);
        }
    }

    private function ensureRectorClassIsValid(string $rectorClass, string $methodName): void
    {
        if (is_a($rectorClass, $this->getRectorInterface(), true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" in "%s()" method must be type of "%s"',
            $rectorClass,
            $methodName,
            $this->getRectorInterface()
        ));
    }
}
