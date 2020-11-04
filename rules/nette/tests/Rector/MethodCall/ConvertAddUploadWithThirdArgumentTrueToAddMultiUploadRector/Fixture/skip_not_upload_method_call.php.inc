<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector\Fixture;

use Nette\Forms\Form;

final class SkipSomeClass3
{
    public function upload()
    {
        $form = new Form();
        $form->setHtmlAttribute('a', 'a');
    }
}

?>
