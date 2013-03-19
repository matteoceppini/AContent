<?php
define('TR_INCLUDE_PATH', '../include/');
require_once(TR_INCLUDE_PATH.'vitals.inc.php');
require_once("common.inc.php");

try {
  $req = OAuthRequest::from_request();
  $token = $oauth_server->fetch_request_token($req);
  print $token;
} catch (OAuthException $e) {
  print($e->getMessage() . "\n<hr />\n");
  print_r($req);
  die();
}

?>
