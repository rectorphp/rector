<?php

declare (strict_types=1);
namespace Rector\Nette\Set;

use Rector\Set\Contract\SetListInterface;
final class KdybySetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const KDYBY_EVENTS_TO_CONTRIBUTTE_EVENT_DISPATCHER = __DIR__ . '/../../config/sets/kdyby/kdyby-events-to-contributte-event-dispatcher.php';
    /**
     * @var string
     */
    public const KDYBY_TRANSLATOR_TO_CONTRIBUTTE_TRANSLATION = __DIR__ . '/../../config/sets/kdyby/kdyby-translator-to-contributte-translation.php';
    /**
     * @var string
     */
    public const KDYBY_RABBIT_MQ_TO_SYMFONY = __DIR__ . '/../../config/sets/kdyby/kdyby-rabbit-mq-to-symfony.php';
}
