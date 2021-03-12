<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

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
