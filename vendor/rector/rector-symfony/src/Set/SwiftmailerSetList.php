<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Set;

use RectorPrefix20220606\Rector\Set\Contract\SetListInterface;
final class SwiftmailerSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const SWIFTMAILER_60 = __DIR__ . '/../../config/sets/swiftmailer/swiftmailer60.php';
    /**
     * @var string
     * @see https://symfony.com/blog/the-end-of-swiftmailer
     */
    public const SWIFTMAILER_TO_SYMFONY_MAILER = __DIR__ . '/../../config/sets/swiftmailer/swiftmailer-to-symfony-mailer.php';
}
