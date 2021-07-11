<?php

namespace Rector\Tests\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector\Fixture;

$code = '<?php echo 1;';
$tokens = \PhpToken::tokenize($code);

foreach ($tokens as $token) {
   $name = $token->getTokenName();
   $text = $token->text;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector\Fixture;

$code = '<?php echo 1;';
$tokens = token_get_all($code);

foreach ($tokens as $token) {
   $name = is_array($token) ? token_name($token[0]) : null;
   $text = is_array($token) ? $token[1] : $token;
}

?>
