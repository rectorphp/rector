<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\AssertChoice;

use Symfony\Component\Validator\Constraints as Assert;

class AssertChoiceWithManyGroups
{
    /**
     * @Assert\Choice(callback={"App\Entity\Genre", "getGenres"}, groups={"registration", "again"})
     */
    private $ratingType;
}
