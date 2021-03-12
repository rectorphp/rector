<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\AMP;
use DateTime;

class KeepCorrect
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var AMP
     */
    private $amp;

    /**
     * @var DateTime
     */
    private $letMeBe;

    /**
     * @var \DateTimeInterface
     */
    private $letMeBeAsWell;

    /**
     * @var \Nette\Utils\DateTime
     */
    private $letMeBeAsForever;
}
