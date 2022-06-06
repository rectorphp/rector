<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Set;

use RectorPrefix20220606\Rector\Set\Contract\SetListInterface;
final class JMSSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const REMOVE_JMS_INJECT = __DIR__ . '/../../config/sets/jms/remove-jms-inject.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/jms/annotations-to-attributes.php';
}
