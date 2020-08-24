<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\AMP;
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
