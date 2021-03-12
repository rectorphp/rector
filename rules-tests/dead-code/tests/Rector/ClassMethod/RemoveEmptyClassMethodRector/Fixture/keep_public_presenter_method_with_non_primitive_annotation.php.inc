<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

use Nette\Application\UI\Presenter;

final class KeepPublicPresenterMethodWithoutPrimitiveAnnotation extends Presenter
{
    /**
     * @User(secured)
     */
    public function keep()
    {
    }
}
