<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Idiosyncratic\EditorConfig;

use ErrorException;
use RectorPrefix20211231\Idiosyncratic\EditorConfig\Declaration\Factory;
use function array_key_exists;
use function debug_backtrace;
use function explode;
use function fnmatch;
use function implode;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function str_replace;
use const PREG_SET_ORDER;
final class Section
{
    /** @var string */
    private $globPrefix;
    /** @var string */
    private $glob;
    /** @var array<string, mixed> */
    private $declarations = [];
    /** @var Factory */
    private $declarationFactory;
    /**
     * @param array<string, mixed> $declarations
     */
    public function __construct(string $globPrefix, string $glob, array $declarations, \RectorPrefix20211231\Idiosyncratic\EditorConfig\Declaration\Factory $declarationFactory)
    {
        $this->globPrefix = $globPrefix;
        $this->glob = $glob;
        $this->declarationFactory = $declarationFactory;
        $this->setDeclarations($declarations);
    }
    public function __toString() : string
    {
        return \sprintf("[%s]\n%s\n", $this->glob, \implode("\n", $this->getDeclarations()));
    }
    /**
     * @return array<string, mixed>
     */
    public function getDeclarations() : array
    {
        return $this->declarations;
    }
    public function matches(string $path) : bool
    {
        // normalize path to unix-style directory separator,
        // because the glob pattern assumes linux-style directory separators
        $path = \str_replace('\\', '/', $path);
        if (\preg_match('#{(.*)}#', $this->glob) === 1) {
            return $this->matchesWithCurlBracesExpansion($path);
        }
        $pattern = \sprintf('%s%s', $this->globPrefix, $this->glob);
        return \fnmatch($pattern, $path);
    }
    /**
     * @param array<string, mixed> $declarations
     */
    private function setDeclarations(array $declarations) : void
    {
        foreach ($declarations as $name => $value) {
            $this->setDeclaration($name, $value);
        }
    }
    /**
     * @param mixed $value
     */
    private function setDeclaration(string $name, $value) : void
    {
        $declaration = $this->declarationFactory->getDeclaration($name, $value);
        $this->declarations[$declaration->getName()] = $declaration;
    }
    /**
     * @return mixed
     */
    public function __get(string $property)
    {
        if (isset($this->declarations[$property]) === \true) {
            return $this->declarations[$property];
        }
        $trace = \debug_backtrace();
        throw new \ErrorException(\sprintf('Undefined property: %s in %s on line %s', $property, $trace[0]['file'], $trace[0]['line']));
    }
    public function __isset(string $property) : bool
    {
        return \array_key_exists($property, $this->declarations);
    }
    private function matchesWithCurlBracesExpansion(string $path) : bool
    {
        \preg_match_all('#(?<prefix>.*){(?<subpattern>.*)}#', $this->glob, $matches, \PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (\array_key_exists('subpattern', $match) === \false) {
                continue;
            }
            $subPatterns = \explode(',', $match['subpattern']);
            foreach ($subPatterns as $subPattern) {
                $pattern = \sprintf('%s%s', $this->globPrefix, $match['prefix'] . $subPattern);
                if (\fnmatch($pattern, $path)) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
