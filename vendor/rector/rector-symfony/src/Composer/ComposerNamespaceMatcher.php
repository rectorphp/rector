<?php

declare (strict_types=1);
namespace Rector\Symfony\Composer;

use RectorPrefix20211020\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem;
final class ComposerNamespaceMatcher
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Symplify\ComposerJsonManipulator\ComposerJsonFactory
     */
    private $composerJsonFactory;
    public function __construct(\RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \RectorPrefix20211020\Symplify\ComposerJsonManipulator\ComposerJsonFactory $composerJsonFactory)
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->composerJsonFactory = $composerJsonFactory;
    }
    public function matchNamespaceForLocation(string $path) : ?string
    {
        $composerJsonFilePath = \getcwd() . '/composer.json';
        if (!$this->smartFileSystem->exists($composerJsonFilePath)) {
            return null;
        }
        $composerJson = $this->composerJsonFactory->createFromFilePath($composerJsonFilePath);
        $autoload = $composerJson->getAutoload();
        foreach ($autoload['psr-4'] ?? [] as $namespace => $directory) {
            if (!\is_array($directory)) {
                $directory = [$directory];
            }
            foreach ($directory as $singleDirectory) {
                if (\strncmp($path, $singleDirectory, \strlen($singleDirectory)) !== 0) {
                    continue;
                }
                return \rtrim($namespace, '\\');
            }
        }
        return null;
    }
}
