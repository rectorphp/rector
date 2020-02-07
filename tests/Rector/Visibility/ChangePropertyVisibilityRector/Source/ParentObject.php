<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source;

class ParentObject
{
    public $toBePublicProperty;
    protected $toBeProtectedProperty;
    private $toBePrivateProperty;
}
