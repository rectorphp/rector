<?php

declare (strict_types=1);
namespace PhpParser\Lexer\TokenEmulator;

/** @internal */
abstract class TokenEmulator
{
    public abstract function getPhpVersion() : string;
    /**
     * @param string $code
     */
    public abstract function isEmulationNeeded($code) : bool;
    /**
     * @return array Modified Tokens
     * @param string $code
     * @param mixed[] $tokens
     */
    public abstract function emulate($code, $tokens) : array;
    /**
     * @return array Modified Tokens
     * @param string $code
     * @param mixed[] $tokens
     */
    public abstract function reverseEmulate($code, $tokens) : array;
    /**
     * @param string $code
     * @param mixed[] $patches
     */
    public function preprocessCode($code, &$patches) : string
    {
        return $code;
    }
}
