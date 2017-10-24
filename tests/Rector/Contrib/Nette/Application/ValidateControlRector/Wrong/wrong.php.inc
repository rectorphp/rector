<?php declare (strict_types=1);

final class CustomControl extends Nette\Application\UI\Control
{

}

$myControl = new CustomControl;
$myControl->validateControl();
$myControl->validateControl('snippet');
