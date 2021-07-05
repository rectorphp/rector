<?php

declare (strict_types=1);
namespace PhpParser\Lexer\TokenEmulator;

/**
 * Reverses emulation direction of the inner emulator.
 */
final class ReverseEmulator extends \PhpParser\Lexer\TokenEmulator\TokenEmulator
{
    /** @var TokenEmulator Inner emulator */
    private $emulator;
    public function __construct(\PhpParser\Lexer\TokenEmulator\TokenEmulator $emulator)
    {
        $this->emulator = $emulator;
    }
    public function getPhpVersion() : string
    {
        return $this->emulator->getPhpVersion();
    }
    /**
     * @param string $code
     */
    public function isEmulationNeeded($code) : bool
    {
        return $this->emulator->isEmulationNeeded($code);
    }
    /**
     * @param string $code
     * @param mixed[] $tokens
     */
    public function emulate($code, $tokens) : array
    {
        return $this->emulator->reverseEmulate($code, $tokens);
    }
    /**
     * @param string $code
     * @param mixed[] $tokens
     */
    public function reverseEmulate($code, $tokens) : array
    {
        return $this->emulator->emulate($code, $tokens);
    }
    /**
     * @param string $code
     * @param mixed[] $patches
     */
    public function preprocessCode($code, &$patches) : string
    {
        return $code;
    }
}
