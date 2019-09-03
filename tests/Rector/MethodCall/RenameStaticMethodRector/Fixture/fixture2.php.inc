<?php

use Rector\Tests\Rector\MethodCall\RenameStaticMethodRector\Source\FormMacros;

function staticMethodNameReplacer2()
{
    $result = FormMacros::renderFormBegin($someArgs);
    $result = FormMACROS::renderFormBEGIN($someArgs);
}

?>
-----
<?php

use Rector\Tests\Rector\MethodCall\RenameStaticMethodRector\Source\FormMacros;

function staticMethodNameReplacer2()
{
    $result = Nette\Bridges\FormsLatte\Runtime::renderFormBegin($someArgs);
    $result = Nette\Bridges\FormsLatte\Runtime::renderFormBegin($someArgs);
}

?>
