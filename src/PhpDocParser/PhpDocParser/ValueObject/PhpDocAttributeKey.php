<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\PhpDocParser\ValueObject;

use PHPStan\PhpDocParser\Ast\Attribute;
final class PhpDocAttributeKey
{
    /**
     * @var string
     */
    public const PARENT = 'parent';
    /**
     * @var string
     */
    public const ORIG_NODE = Attribute::ORIGINAL_NODE;
}
