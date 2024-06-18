<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class SwiftMailerSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const SWIFT_TO_SYMFONY = __DIR__ . '/../../config/sets/swiftmailer/swiftmailer-to-symfony-mailer.php';
}
