<?php

namespace Rector\Core\Tests\Issues\Issue1225\Fixture;

class Issue1225
{
    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(): void
    {
        echo $this->twig->render(
        // ...
        );
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue1225\Fixture;

class Issue1225
{
    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(): void
    {
        echo $this->twig->render(
        // ...
        );
    }
}

?>
