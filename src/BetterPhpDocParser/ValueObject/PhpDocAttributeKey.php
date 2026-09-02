<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject;

use Rector\PhpDocParser\PhpDocParser\ValueObject\PhpDocAttributeKey as NativePhpDocAttributeKey;
final class PhpDocAttributeKey
{
    /**
     * @var string
     */
    public const START_AND_END = 'start_and_end';
    /**
     * Fully qualified name of identifier type class
     * @var string
     */
    public const RESOLVED_CLASS = 'resolved_class';
    /**
     * Fully qualified name of class referenced in an array item key, e.g. SomeEnum::TOTP
     * @var string
     */
    public const RESOLVED_KEY_CLASS = 'resolved_key_class';
    /**
     * @var string
     */
    public const PARENT = NativePhpDocAttributeKey::PARENT;
    /**
     * @var string
     */
    public const LAST_PHP_DOC_TOKEN_POSITION = 'last_token_position';
    /**
     * @var string
     */
    public const ORIG_NODE = NativePhpDocAttributeKey::ORIG_NODE;
}
