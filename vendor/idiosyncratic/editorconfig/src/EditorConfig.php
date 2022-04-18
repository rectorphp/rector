<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Idiosyncratic\EditorConfig;

use function array_merge;
use function array_pop;
use function dirname;
use function implode;
use function is_file;
use function is_readable;
use function realpath;
use function sprintf;
use const DIRECTORY_SEPARATOR;
final class EditorConfig
{
    /** @var array<string, EditorConfigFile> */
    private $configFiles = [];
    /**
     * @return array<string, mixed>
     */
    public function getConfigForPath(string $path) : array
    {
        $configFiles = $this->locateConfigFiles($path);
        $root = \false;
        $configuration = [];
        $configFile = \array_pop($configFiles);
        while ($configFile !== null) {
            $configuration = \array_merge($configuration, $configFile->getConfigForPath($path));
            $configFile = \array_pop($configFiles);
        }
        foreach ($configuration as $key => $declaration) {
            if ($declaration->getValue() !== null) {
                continue;
            }
            unset($configuration[$key]);
        }
        return $configuration;
    }
    public function printConfigForPath(string $path) : string
    {
        $config = $this->getConfigForPath($path);
        return \implode("\n", $config);
    }
    /**
     * @return array<EditorConfigFile>
     */
    private function locateConfigFiles(string $path) : array
    {
        $files = [];
        $stop = \false;
        $parent = '';
        while ($parent !== $path) {
            $editorConfigFile = \realpath(\sprintf('%s%s.editorconfig', $path, \DIRECTORY_SEPARATOR));
            if ($editorConfigFile !== \false && \is_file($editorConfigFile) && \is_readable($editorConfigFile)) {
                $file = $this->getConfigFile($editorConfigFile);
                $files[] = $file;
                if ($file->isRoot() === \true) {
                    break;
                }
            }
            $path = \dirname($path);
            $parent = \dirname($path);
        }
        return $files;
    }
    private function getConfigFile(string $path) : \RectorPrefix20220418\Idiosyncratic\EditorConfig\EditorConfigFile
    {
        return $this->configFiles[$path] ?? ($this->configFiles[$path] = new \RectorPrefix20220418\Idiosyncratic\EditorConfig\EditorConfigFile($path));
    }
}
