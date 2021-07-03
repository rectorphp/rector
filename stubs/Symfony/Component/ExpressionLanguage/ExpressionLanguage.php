<?php

declare(strict_types=1);

// faking expression language, to be able to downgrade vendor/symfony/dependency-injection/ContainerBuilder.php,
// because it has optional-dependency on expression language

namespace Symfony\Component\ExpressionLanguage;

if (class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
    return;
}

class ExpressionLanguage
{
}
