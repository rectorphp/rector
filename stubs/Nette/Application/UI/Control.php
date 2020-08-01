<?php

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette\ComponentModel\Component;
use Nette\ComponentModel\Container;

if (class_exists('Nette\Application\UI\Control')) {
    return;
}

class Control extends Component
{
    public function getPresenter(): ?Presenter
    {
    }
}
