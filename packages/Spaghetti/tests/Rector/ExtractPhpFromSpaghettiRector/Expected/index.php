<?php
class IndexController
{
    public function render()
    {
        return ['variable1' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']];
    }
}

$variables = (new IndexController())->render();
extract($variables);

?>
<ul>
    <li><a href="<?php echo $variable1; ?>">Odkaz</a>
</ul>
