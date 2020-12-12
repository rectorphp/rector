<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        $callback = create_function('$matches', "return strtolower(\$matches[1]);");

        $delimiter = '___';
        $callback = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");

        $callback = create_function( '$caps', "return '$delimiter';" );

        add_action(
            'widgets_init',
            create_function(
                '',
                'return register_widget( "WordAds_Sidebar_Widget" );'
            )
        );

        $all_ids = array_map( create_function( '$o', 'return $o->ID;' ), $posts);

        $missing = create_function( '$m', 'return $m->type === \'post\';');

        create_function('$caps', "return '$caps';");
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        $callback = function ($matches) {
            return strtolower($matches[1]);
        };

        $delimiter = '___';
        $callback = function ($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };

        $callback = function ($caps) use ($delimiter) {
            return $delimiter;
        };

        add_action(
            'widgets_init',
            function () {
                return register_widget("WordAds_Sidebar_Widget");
            }
        );

        $all_ids = array_map( function ($o) {
            return $o->ID;
        }, $posts);

        $missing = function ($m) {
            return $m->type === 'post';
        };

        function ($caps) {
            return $caps;
        };
    }
}

?>
