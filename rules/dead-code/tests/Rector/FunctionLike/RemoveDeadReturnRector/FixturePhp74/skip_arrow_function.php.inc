<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector\FixturePhp74;

use Nette\Utils\Strings;
use stdClass;

class SkipArrowFunction
{
    private function filterPostTweets(array $tweets): array
    {
        return array_filter($tweets, fn(stdClass $publishedTweet) => Strings::match($publishedTweet->getText(), '#New post on#i'));
     }
}
