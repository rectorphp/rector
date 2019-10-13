<?php

declare(strict_types=1);

namespace Symfony\Component\Translation;

if (class_exists('Symfony\Component\Translation\Translator')) {
    return;
}

class Translator implements TranslatorInterface
{

}
