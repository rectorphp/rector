<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202304\Symfony\Component\DependencyInjection\Attribute;

use RectorPrefix202304\Symfony\Component\DependencyInjection\Exception\LogicException;
use RectorPrefix202304\Symfony\Component\DependencyInjection\Reference;
use RectorPrefix202304\Symfony\Component\ExpressionLanguage\Expression;
/**
 * Attribute to tell a parameter how to be autowired.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Autowire
{
    /**
     * @readonly
     * @var string|\Symfony\Component\ExpressionLanguage\Expression|\Symfony\Component\DependencyInjection\Reference
     */
    public $value;
    /**
     * Use only ONE of the following.
     *
     * @param string|null $value      Parameter value (ie "%kernel.project_dir%/some/path")
     * @param string|null $service    Service ID (ie "some.service")
     * @param string|null $expression Expression (ie 'service("some.service").someMethod()')
     */
    public function __construct(string $value = null, string $service = null, string $expression = null)
    {
        if (!($service xor $expression xor null !== $value)) {
            throw new LogicException('#[Autowire] attribute must declare exactly one of $service, $expression, or $value.');
        }
        if (null !== $value && \strncmp($value, '@', \strlen('@')) === 0) {
            switch (\true) {
                case \strncmp($value, '@@', \strlen('@@')) === 0:
                    $value = \substr($value, 1);
                    break;
                case \strncmp($value, '@=', \strlen('@=')) === 0:
                    $expression = \substr($value, 2);
                    break;
                default:
                    $service = \substr($value, 1);
                    break;
            }
        }
        switch (\true) {
            case null !== $service:
                $this->value = new Reference($service);
                break;
            case null !== $expression:
                if (!\class_exists(Expression::class)) {
                    throw new LogicException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed. Try running "composer require symfony/expression-language".');
                }
                $this->value = new Expression($expression);
                break;
            case null !== $value:
                $this->value = $value;
                break;
        }
    }
}
