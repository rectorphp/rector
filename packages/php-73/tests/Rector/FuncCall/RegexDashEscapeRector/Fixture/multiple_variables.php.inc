<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class MultipleVariables
{
    public function github()
    {
        // https://github.com/dmitry-kulikov/yii2-domain-validator/commit/f35d0f36a9c924ada72767235a17e97840c6dc8a
        if ($this->allowUnderscore) {
            $pattern = '/^[a-z\d-_]+$/i';
        } else {
            $pattern = '/^[a-z\d-]+$/i';
        }
        if (!preg_match($pattern, $label)) {
            return false;
        }
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class MultipleVariables
{
    public function github()
    {
        // https://github.com/dmitry-kulikov/yii2-domain-validator/commit/f35d0f36a9c924ada72767235a17e97840c6dc8a
        if ($this->allowUnderscore) {
            $pattern = '/^[a-z\d\-_]+$/i';
        } else {
            $pattern = '/^[a-z\d-]+$/i';
        }
        if (!preg_match($pattern, $label)) {
            return false;
        }
    }
}

?>
