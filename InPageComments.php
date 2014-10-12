<?php
if(!defined('MEDIAWIKI')) die;

$dir = __DIR__;
$ext = 'InPageComments';

$wgExtensionCredits['other'][] = array(
  'path'            => __FILE__,
  'name'            => $ext,
  'version'         => '0.1',
  'author'          => 'uta',
  'url'             => 'https://github.com/uta/InPageComments',
  'descriptionmsg'  => 'in-page-comments-desc',
  'license-name'    => 'MIT-License',
);

$wgResourceModules['ext.InPageComments'] = array(
  'localBasePath'   => "$dir/resources/",
  'remoteExtPath'   => "$ext/resources/",
  'messages'        => array('in-page-comments-message-succeeded'),
  'scripts'         => array('ext.InPageComments.js'),
);

$wgAutoloadClasses["${ext}Hooks"]   = "$dir/classes/${ext}Hooks.php";
$wgAutoloadClasses["${ext}Special"] = "$dir/classes/${ext}Special.php";
$wgAutoloadClasses["${ext}Util"]    = "$dir/classes/${ext}Util.php";
$wgExtensionMessagesFiles[$ext]     = "$dir/i18n/_backward_compatibility.php";
$wgHooks['BeforePageDisplay'][]     = "${ext}Hooks::renderResources";
$wgHooks['ParserFirstCallInit'][]   = "${ext}Hooks::setParserHook";
$wgMessagesDirs[$ext]               = "$dir/i18n";
$wgSpecialPages[$ext]               = "${ext}Special";

$wgInPageCommentsTag                = 'comments';
$wgInPageCommentsFormMessage        = 'in-page-comments-form-message';
$wgInPageCommentsFormSubmit         = 'in-page-comments-form-submit';
$wgInPageCommentsAnonDisplay        = 'in-page-comments-anon-display';
$wgInPageCommentsMaxLetters         = 200;
$wgInPageCommentsDenyBlockedUser    = true;
$wgInPageCommentsDenyProtectedPage  = true;
$wgInPageCommentsSpamCheckEncoding  = true;
$wgInPageCommentsSpamCheckReferer   = true;
$wgInPageCommentsSpamCheckUrlCount  = 2;
$wgInPageCommentsDebugMode          = true;
