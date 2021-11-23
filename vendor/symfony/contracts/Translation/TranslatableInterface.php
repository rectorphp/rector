<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211123\Symfony\Contracts\Translation;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface TranslatableInterface
{
    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param string|null $locale
     */
    public function trans($translator, $locale = null) : string;
}
