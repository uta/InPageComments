<?php
if(!defined('MEDIAWIKI')) die;

$dir = __DIR__;
$ext = 'InPageComments';

$wgExtensionCredits['other'][] = array(
  'path'            => __FILE__,
  'name'            => $ext,
  'version'         => '0.1',
  'author'          => '[https://github.com/uta uta]',
  'url'             => 'https://github.com/uta/InPageComments',
  'descriptionmsg'  => 'in-page-comments-desc',
  'license-name'    => 'MIT-License',
);

$wgResourceModules['ext.InPageComments'] = array(
  'localBasePath'   => "$dir/resources/",
  'remoteExtPath'   => "$ext/resources/",
  'messages'        => array('in-page-comments-form-message', 'in-page-comments-message-posting', 'in-page-comments-message-succeeded'),
  'scripts'         => array('ext.InPageComments.js'),
  'styles'          => array('ext.InPageComments.less' => array('media' => 'all')),
);

$wgAPIModules[$ext]                 = "${ext}Api";
$wgAutoloadClasses[$ext]            = "$dir/classes/$ext.php";
$wgAutoloadClasses["${ext}Api"]     = "$dir/classes/${ext}Api.php";
$wgAutoloadClasses["${ext}Hooks"]   = "$dir/classes/${ext}Hooks.php";
$wgAutoloadClasses["${ext}Util"]    = "$dir/classes/${ext}Util.php";
$wgExtensionMessagesFiles[$ext]     = "$dir/i18n/_backward_compatibility.php";
$wgHooks['BeforePageDisplay'][]     = "${ext}Hooks::renderResources";
$wgHooks['ParserFirstCallInit'][]   = "${ext}Hooks::setParserHook";
$wgMessagesDirs[$ext]               = "$dir/i18n";

$wgInPageCommentsTag                = 'comments';
$wgInPageCommentsAnonDisplay        = 'in-page-comments-anon-display';
$wgInPageCommentsFormMessage        = 'in-page-comments-form-message';
$wgInPageCommentsUrlFontAwesome     = '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css';
$wgInPageCommentsMaxLetters         = 200;
$wgInPageCommentsDenyBlockedUser    = true;
$wgInPageCommentsDenyProtectedPage  = true;
$wgInPageCommentsSpamCheckEncoding  = true;
$wgInPageCommentsSpamCheckReferer   = true;
$wgInPageCommentsSpamCheckUrlCount  = 2;
