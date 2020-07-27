<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;

class RenameVariousLetterCasing extends AbstractType
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        parent::setDEFAULTOptions($resolver);
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;

class RenameVariousLetterCasing extends AbstractType
{
    public function configureOptions(SomeResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}

?>
