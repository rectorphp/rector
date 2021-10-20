<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\VarDumper\Cloner;

/**
 * Represents the current state of a dumper while dumping.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Cursor
{
    public const HASH_INDEXED = \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub::ARRAY_INDEXED;
    public const HASH_ASSOC = \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub::ARRAY_ASSOC;
    public const HASH_OBJECT = \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub::TYPE_OBJECT;
    public const HASH_RESOURCE = \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub::TYPE_RESOURCE;
    public $depth = 0;
    public $refIndex = 0;
    public $softRefTo = 0;
    public $softRefCount = 0;
    public $softRefHandle = 0;
    public $hardRefTo = 0;
    public $hardRefCount = 0;
    public $hardRefHandle = 0;
    public $hashType;
    public $hashKey;
    public $hashKeyIsBinary;
    public $hashIndex = 0;
    public $hashLength = 0;
    public $hashCut = 0;
    public $stop = \false;
    public $attr = [];
    public $skipChildren = \false;
}
