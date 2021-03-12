<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Stackoverflow
{
    public function run()
    {
        $func = create_function('$atts, $content = null','return "<div class=\"' . $class_list . '\">" . do_shortcode($content) . "</div>";' );

        add_shortcode($shortcode, $func);

        $this->translation_plural['callable'] = create_function('$n', $this->translation_plural['function']);

        $newfunc = create_function('$a,$b', 'return "ln($a) + ln($b) = " . log($a * $b);');
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Stackoverflow
{
    public function run()
    {
        $func = function ($atts, $content = null) use ($class_list) {
            return "<div class=\"{$class_list}\">" . do_shortcode($content) . "</div>";
        };

        add_shortcode($shortcode, $func);

        $this->translation_plural['callable'] = function ($n) {
            eval($this->translation_plural['function']);
        };

        $newfunc = function ($a, $b) {
            return "ln({$a}) + ln({$b}) = " . log($a * $b);
        };
    }
}

?>
