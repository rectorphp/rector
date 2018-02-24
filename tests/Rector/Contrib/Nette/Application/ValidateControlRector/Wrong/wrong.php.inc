<?php declare (strict_types=1);

namespace {
    final class CustomControl extends Stub_Nette\Application\UI\Control
    {

    }

    $myControl = new CustomControl;
    $myControl->validateControl();
    $myControl->validateControl('snippet');
}


namespace Stub_Nette\Application\UI
{
    class Control
    {

    }
}
