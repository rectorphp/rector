<?php declare(strict_types=1);

namespace Rector\Node;

/**
 * List of attributes by constants, to prevent any typos.
 *
 * Because typo can causing return "null" instaed of real value - impossible to spot.
 */
final class Attribute
{
    /**
     * @var string
     */
    public const COMMENTS = 'comments';
}
