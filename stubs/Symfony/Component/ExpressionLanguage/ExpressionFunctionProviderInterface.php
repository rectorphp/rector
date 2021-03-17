<?php

declare(strict_types=1);

// faking expression language, to be able to downgrade vendor/symfony/dependency-injection/ContainerBuilder.php,
// because it has optional-dependency on expression language

namespace Symfony\Component\ExpressionLanguage;

if (interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
    return;
}

interface ExpressionFunctionProviderInterface
{

}
