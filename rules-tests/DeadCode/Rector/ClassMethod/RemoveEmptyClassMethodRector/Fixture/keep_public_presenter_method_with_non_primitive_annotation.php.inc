<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

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
