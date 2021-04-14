<?php

class NoNamespace
{
    public function run()
    {
        return new class {
            public function execute()
            {
            }
        };
    }
}
-----
<?php

class Anonymous
{
    public function execute()
    {
    }
}
class NoNamespace
{
    public function run()
    {
        return new Anonymous();
    }
}