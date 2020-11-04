<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector\Fixture;

use Nette\Forms\Form;

final class SkipSomeClass
{
    public function upload()
    {
        $form = new Form();
        $form->addUpload('a', 'a');
    }
}

?>
