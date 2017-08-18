<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use Nette\Utils\Html;

final class VariableType
{
    /**
     * @return Html
     */
    public function prepare(): \Nette\Utils\Html
    {
        $html = new Html;
        $assignedHtml = $html;

        return $assignedHtml;
    }
}
