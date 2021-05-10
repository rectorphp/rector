<?php

declare (strict_types=1);
namespace Rector\PHPUnit\TestClassResolver;

use Rector\PHPUnit\Composer\ComposerAutoloadedDirectoryProvider;
use Rector\PHPUnit\RobotLoader\RobotLoaderFactory;
final class PHPUnitTestCaseClassesProvider
{
    /**
     * @var string[]
     */
    private $phpUnitTestCaseClasses = [];
    /**
     * @var ComposerAutoloadedDirectoryProvider
     */
    private $composerAutoloadedDirectoryProvider;
    /**
     * @var RobotLoaderFactory
     */
    private $robotLoaderFactory;
    public function __construct(\Rector\PHPUnit\Composer\ComposerAutoloadedDirectoryProvider $composerAutoloadedDirectoryProvider, \Rector\PHPUnit\RobotLoader\RobotLoaderFactory $robotLoaderFactory)
    {
        $this->composerAutoloadedDirectoryProvider = $composerAutoloadedDirectoryProvider;
        $this->robotLoaderFactory = $robotLoaderFactory;
    }
    /**
     * @return string[]
     */
    public function provide() : array
    {
        if ($this->phpUnitTestCaseClasses !== []) {
            return $this->phpUnitTestCaseClasses;
        }
        $directories = $this->composerAutoloadedDirectoryProvider->provide();
        $robotLoader = $this->robotLoaderFactory->createFromDirectories($directories);
        $robotLoader->rebuild();
        foreach (\array_keys($robotLoader->getIndexedClasses()) as $className) {
            $this->phpUnitTestCaseClasses[] = $className;
        }
        return $this->phpUnitTestCaseClasses;
    }
}
