<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220209\Nette\Neon;

/**
 * Simple parser & generator for Nette Object Notation.
 * @see https://ne-on.org
 */
final class Neon
{
    public const BLOCK = \RectorPrefix20220209\Nette\Neon\Encoder::BLOCK;
    public const CHAIN = '!!chain';
    /**
     * Returns value converted to NEON.
     */
    public static function encode($value, bool $blockMode = \false, string $indentation = "\t") : string
    {
        $encoder = new \RectorPrefix20220209\Nette\Neon\Encoder();
        $encoder->blockMode = $blockMode;
        $encoder->indentation = $indentation;
        return $encoder->encode($value);
    }
    /**
     * Converts given NEON to PHP value.
     * @return mixed
     */
    public static function decode(string $input)
    {
        $decoder = new \RectorPrefix20220209\Nette\Neon\Decoder();
        return $decoder->decode($input);
    }
    /**
     * Converts given NEON file to PHP value.
     * @return mixed
     */
    public static function decodeFile(string $file)
    {
        if (!\is_file($file)) {
            throw new \RectorPrefix20220209\Nette\Neon\Exception("File '{$file}' does not exist.");
        }
        $input = \file_get_contents($file);
        if (\substr($input, 0, 3) === "﻿") {
            // BOM
            $input = \substr($input, 3);
        }
        return self::decode($input);
    }
}
