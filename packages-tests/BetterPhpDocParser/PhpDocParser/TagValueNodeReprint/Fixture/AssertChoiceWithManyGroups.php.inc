<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\AssertChoice;

use Symfony\Component\Validator\Constraints as Assert;

class AssertChoiceWithManyGroups
{
    /**
     * @Assert\Choice(callback={"App\Entity\Genre", "getGenres"}, groups={"registration", "again"})
     */
    private $ratingType;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Symfony\Component\Validator\Constraints\Choice
