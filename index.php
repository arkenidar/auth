<?php
@session_start();

// https://github.com/facebook/php-graph-sdk

//composer require facebook/graph-sdk
require_once __DIR__ .'/vendor/autoload.php';
require 'app_settings.php';
function fb(){
require 'app_settings.php';
$fb = new \Facebook\Facebook([
  'app_id' => $app_id,
  'app_secret' => $app_secret,
  'default_graph_version' => 'v2.10',
]);
//////////////////////////////////////////////////
// https://developers.facebook.com/docs/php/howto/example_access_token_from_javascript/
$helper = $fb->getJavaScriptHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  return ['Graph returned an error: ' . $e->getMessage(), false];
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  return ['Facebook SDK returned an error: ' . $e->getMessage(), false];
}
if (! isset($accessToken)) {
  $_SESSION['username']='';
  return ['Please login...', false];
}
// Logged in
$_SESSION['fb_access_token'] = (string) $accessToken;
///////////////////////////
try {
  $response = $fb->get('/me', $_SESSION['fb_access_token']);
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  return ['Graph returned an error: ' . $e->getMessage(), false];
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  return ['Facebook SDK returned an error: ' . $e->getMessage(), false];
}
$me = $response->getGraphUser();
$_SESSION['username']=$me->getName();
return ['Logged in as ' . $me->getName(), true];
}
$produced = fb();
?>
<!DOCTYPE html><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Facebook Login in JS+PHP</title>
<script>
function fbAsyncInit(){
FB.init({
appId: '<?=$app_id?>',
cookie: true,
xfbml: true,
version: 'v3.2'
})
FB.getLoginStatus(console.log)
if(window.relogin) FB.login(function(response){location.reload()})
//FB.api('/me',response=>alert(response.name))
}
</script>
<script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>
<?php
$relogin_cases=['Graph returned an error: This authorization code has expired.',
'Graph returned an error: This authorization code has been used.'];
$relogin=in_array($produced[0],$relogin_cases)?'true':'false';
?>
<script>relogin=<?=$relogin?></script>
<?php $action=$produced[1]?'logout':'login'; ?>
<button onclick="FB.<?=$action?>(function(response){location.reload()})"><?=$action?></button>
<?php if($_SESSION['username']!=''){?>
<a href="/php_http/chat/">chat as: <?=$_SESSION['username']?></a>
<?php }?>
