<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class SkipCallbackWithString
{
    /**
     * @Assert\Choice(callback="getChoices")
     */
    private $attribute;

    public function getChoices(): array
    {
        return ['First Choice', 'Second Choice'];
    }
}

?>
