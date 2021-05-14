<?php

namespace RectorPrefix20210514\spec\PrettyXml;

use RectorPrefix20210514\PhpSpec\ObjectBehavior;
class FormatterSpec extends \RectorPrefix20210514\PhpSpec\ObjectBehavior
{
    function it_should_indent_a_nested_element()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar>Baz</bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar>Baz</bar>
</foo>
XML
);
    }
    function it_should_indent_a_very_nested_element()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar><bacon><bob>Baz</bob></bacon></bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar>
        <bacon>
            <bob>Baz</bob>
        </bacon>
    </bar>
</foo>
XML
);
    }
    function it_should_indent_two_nested_elements()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar>Baz</bar><egg>Bacon</egg></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar>Baz</bar>
    <egg>Bacon</egg>
</foo>
XML
);
    }
    function it_should_indent_a_nested_empty_element()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar /></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar />
</foo>
XML
);
    }
    function it_should_indent_double_nested_elements()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar><egg /></bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar>
        <egg />
    </bar>
</foo>
XML
);
    }
    function it_should_indent_a_nested_element_with_an_attribute()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar a="b">Baz</bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar a="b">Baz</bar>
</foo>
XML
);
    }
    function it_should_indent_a_nested_element_when_parent_has_attributes()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo a="b"><bar>Baz</bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo a="b">
    <bar>Baz</bar>
</foo>
XML
);
    }
    function it_should_change_the_size_of_the_indent()
    {
        $this->setIndentSize(2);
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar>Baz</bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
  <bar>Baz</bar>
</foo>
XML
);
    }
    function it_should_change_the_indent_character()
    {
        $this->setIndentCharacter('_');
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><bar>Baz</bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
____<bar>Baz</bar>
</foo>
XML
);
    }
    function it_should_remove_existing_excess_whitespace()
    {
        $this->format(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar>Baz</bar>
    <egg>
                <bacon>Yum</bacon>
                        </egg>
</foo>
XML
)->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar>Baz</bar>
    <egg>
        <bacon>Yum</bacon>
    </egg>
</foo>
XML
);
    }
    function it_respects_whitespace_in_cdata_tags()
    {
        $this->format(<<<XML
<?xml version="1.0" encoding="UTF-8"?><foo>
  <bar><![CDATA[some
whitespaced   words
      blah]]></bar></foo>
XML
)->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <bar>
        <![CDATA[some
whitespaced   words
      blah]]>
    </bar>
</foo>
XML
);
    }
    function it_should_support_underscores_in_tag_names()
    {
        $this->format('<?xml version="1.0" encoding="UTF-8"?><foo><foo_bar>Baz</foo_bar></foo>')->shouldReturn(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo>
    <foo_bar>Baz</foo_bar>
</foo>
XML
);
    }
}
