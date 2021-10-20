<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Idiosyncratic\EditorConfig;

use RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\Factory;
use RectorPrefix20211020\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RuntimeException;
use function array_merge;
use function dirname;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function is_file;
use function is_readable;
use function parse_ini_string;
use function preg_replace;
use function sprintf;
use function strpos;
use const INI_SCANNER_RAW;
final class EditorConfigFile
{
    /** @var string */
    private $path;
    /** @var string */
    private $fileContent = '';
    /** @var bool */
    private $isRoot = \false;
    /** @var array<int, Section> */
    private $sections = [];
    /** @var Factory */
    private $declarationFactory;
    public function __construct(string $path, ?\RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\Factory $declarationFactory = null)
    {
        $this->declarationFactory = $declarationFactory ?? new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\Factory();
        if (\is_file($path) === \false || \is_readable($path) === \false) {
            throw new \RuntimeException(\sprintf('File %s does not exist or is not readable', $path));
        }
        $content = $this->cleanContent($path);
        $this->path = $path;
        $this->parse($content);
    }
    public function __toString() : string
    {
        $preamble = $this->isRoot() === \true ? "root=true\n" : '';
        $sections = [];
        foreach ($this->sections as $section) {
            $sections[] = (string) $section;
        }
        return \sprintf('%s%s', $preamble, \implode("\n", $sections));
    }
    public function isRoot() : bool
    {
        return $this->isRoot;
    }
    public function getPath() : string
    {
        return $this->path;
    }
    /**
     * @return array<string, mixed>
     * @param string $path
     */
    public function getConfigForPath($path) : array
    {
        $configuration = [];
        foreach ($this->sections as $section) {
            if ($section->matches($path) === \false) {
                continue;
            }
            $configuration = \array_merge($configuration, $section->getDeclarations());
        }
        return $configuration;
    }
    private function parse(string $content) : void
    {
        $this->fileContent = $content;
        $content = \preg_replace('/^\\s/m', '', $this->fileContent) ?? $this->fileContent;
        $parsedContent = $this->parseIniString($content);
        if (isset($parsedContent['root']) === \true) {
            $this->setIsRoot($parsedContent['root']);
        }
        foreach ($parsedContent as $glob => $declarations) {
            if (\is_array($declarations) === \false) {
                continue;
            }
            $this->sections[] = new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Section($this->getGlobPrefix($glob), $glob, $declarations, $this->declarationFactory);
        }
    }
    private function setIsRoot(string $isRoot) : void
    {
        if (\in_array($isRoot, ['true', 'false']) === \false) {
            throw new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Exception\InvalidValue('root', $isRoot);
        }
        $this->isRoot = $isRoot === 'true';
    }
    private function getGlobPrefix(string $glob) : string
    {
        return \strpos($glob, '/') === 0 ? \dirname($this->path) : '**/';
    }
    /**
     * @return array<string, mixed>
     */
    private function parseIniString(string $content) : array
    {
        $parsedContent = \parse_ini_string($content, \true, \INI_SCANNER_RAW);
        return \is_array($parsedContent) === \true ? $parsedContent : [];
    }
    private function cleanContent(string $path) : string
    {
        $content = \file_get_contents($path);
        if ($content === \false) {
            return '';
        }
        return \preg_replace('/#.*$/m', '', $content) ?? $content;
    }
}
