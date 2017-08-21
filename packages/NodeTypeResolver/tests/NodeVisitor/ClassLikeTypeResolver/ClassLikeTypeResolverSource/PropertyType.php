<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\ClassLikeTypeResolverSource;

use Nette\Utils\Html;

final class PropertyType
{
    /**
     * @var Html
     */
    private $html;

    public function __construct(Html $html)
    {
        $this->html = $html;
    }

    public function getHtml(): Html
    {
        return $this->html;
    }
}
