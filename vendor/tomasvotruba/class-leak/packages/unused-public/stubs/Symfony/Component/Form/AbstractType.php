<?php

declare (strict_types=1);
namespace RectorPrefix202608\Symfony\Component\Form;

use RectorPrefix202608\Symfony\Component\OptionsResolver\OptionsResolver;
abstract class AbstractType
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
    }
}
