<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\AssertChoice;

use Symfony\Component\Validator\Constraints as Assert;

class AssertChoice
{
    public const RATINGS_DISCRIMINATOR_MAP = [
        '5star' => 'App\Entity\Rating\FiveStar',
        '4star' => 'App\Entity\Rating\FourStar',
    ];

    public const SMALL_ONE = 'small_one';

    /**
     * @Assert\Choice(choices=AssertChoice::RATINGS_DISCRIMINATOR_MAP, groups={AssertChoice::SMALL_ONE})
     */
    private $ratingType;
}
