<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpFoundation;

use RectorPrefix20211020\Symfony\Component\ExpressionLanguage\ExpressionLanguage;
/**
 * ExpressionRequestMatcher uses an expression to match a Request.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExpressionRequestMatcher extends \RectorPrefix20211020\Symfony\Component\HttpFoundation\RequestMatcher
{
    private $language;
    private $expression;
    /**
     * @param \Symfony\Component\ExpressionLanguage\ExpressionLanguage $language
     */
    public function setExpression($language, $expression)
    {
        $this->language = $language;
        $this->expression = $expression;
    }
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function matches($request)
    {
        if (!$this->language) {
            throw new \LogicException('Unable to match the request as the expression language is not available.');
        }
        return $this->language->evaluate($this->expression, ['request' => $request, 'method' => $request->getMethod(), 'path' => \rawurldecode($request->getPathInfo()), 'host' => $request->getHost(), 'ip' => $request->getClientIp(), 'attributes' => $request->attributes->all()]) && parent::matches($request);
    }
}
