<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

final class PhpDocAttributeKey
{
    /**
     * @var string
     */
    public const START_AND_END = 'start_and_end';

    /**
     * @var string
     */
    public const PARENT = \Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::PARENT;

    /**
     * @var string
     */
    public const LAST_PHP_DOC_TOKEN_POSITION = 'last_token_position';

    /**
     * @var string
     */
    public const ORIG_NODE = 'orig_node';
}
