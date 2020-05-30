<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\DataProvider;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeCollector\NodeCollector\ParsedFunctionLikeNodeCollector;

final class GetSubscribedEventsClassMethodProvider
{
    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    public function __construct(ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector)
    {
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
    }

    /**
     * @return ClassMethod[]
     */
    public function provide(): array
    {
        return $this->parsedFunctionLikeNodeCollector->findClassMethodByTypeAndMethod(
            'Kdyby\Events\Subscriber',
            'getSubscribedEvents'
        );
    }
}
