<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

class SkipPresenter
{
    public function actionStop()
    {
    }

    public function renderStop()
    {
    }

    public function handleStop()
    {
    }

    /**
     * @inject
     */
    public function autowireThat()
    {
    }

    public function injectThat()
    {
    }
}
