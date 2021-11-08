<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver\Source;

class Enum
{
    const MODE_ADD = 'add';
    const MODE_EDIT = 'edit';
    const MODE_CLONE = 'clone';

    /**
     * @var self::*
     */
    public $mode;
}
