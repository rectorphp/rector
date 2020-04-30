<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineGeneratedValue;

use Doctrine\ORM\Mapping as ORM;

class GeneratedValueWithStrategyString
{
    /**
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $explicit;
}
