<?php declare (strict_types=1);

final class CustomControl extends Nette\Application\UI\Control
{

}

$myControl = new CustomControl;
$myControl->redrawControl(null, false);
$myControl->redrawControl('snippet', false);
