<?php

declare(strict_types=1);

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

if (class_exists('Symfony\Component\Form\Extension\Core\Type\TextType')) {
    return;
}

class TextType implements FormTypeInterface, FormBuilderInterface
{

}
