<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\AssertChoice;

use Symfony\Component\Validator\Constraints as Assert;

class AssertChoiceQuoteValues
{
    /**
     * @Assert\Choice({"chalet", "apartment"})
     */
    public  $type;
}
