<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

use Rector\Set\Contract\SetListInterface;
final class JMSSetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const REMOVE_JMS_INJECT = __DIR__ . '/../../config/sets/jms/remove-jms-inject.php';
}
