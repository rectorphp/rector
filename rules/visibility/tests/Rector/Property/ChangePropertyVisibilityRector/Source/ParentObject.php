<?php

declare(strict_types=1);

namespace Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\Source;

class ParentObject
{
    public $toBePublicProperty;
    protected $toBeProtectedProperty;
    private $toBePrivateProperty;
}
