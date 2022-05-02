<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\LocaleUtils;

class SkipInAttribute
{
    #[Assert\Choice(callback: [LocaleUtils::class, 'getAllLocales'])]
    private ?string $locale = null;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): Account
    {
        $this->locale = $locale;
        return $this;
    }
}
