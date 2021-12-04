<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

use Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey as NativePhpDocAttributeKey;

final class PhpDocAttributeKey
{
    /**
     * @var string
     */
    final public const START_AND_END = 'start_and_end';

    /**
     * Fully qualified name of identifier type class
     * @var string
     */
    final public const RESOLVED_CLASS = 'resolved_class';

    /**
     * @var string
     */
    final public const PARENT = NativePhpDocAttributeKey::PARENT;

    /**
     * @var string
     */
    final public const LAST_PHP_DOC_TOKEN_POSITION = 'last_token_position';

    /**
     * @var string
     */
    final public const ORIG_NODE = NativePhpDocAttributeKey::ORIG_NODE;
}
