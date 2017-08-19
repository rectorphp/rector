<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeTypeResolverSource;

use Nette\Utils\Html;

final class VariableType
{
    public function prepare(): Html
    {
        $html = new Html;
        $assignedHtml = $html;

        return $assignedHtml;
    }
}
