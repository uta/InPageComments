<?php
if(!defined('MEDIAWIKI')) die;
class InPageCommentsHooks {
  public static function renderCommentForm($text, $params=array(), $parser=null, $frame=false) {
    global $wgInPageCommentsFormMessage, $wgInPageCommentsMaxLetters;
    return Xml::openElement('form', array('action'=>'/', 'method'=>'post', 'id'=>'in_page_comments_form', 'class'=>'integrated'))
         . Xml::input('t', false, $parser->mTitle->getDBKey(), array('type'=>'hidden'))
         . Xml::openElement('button', array('type'=>'submit', 'id'=>'in_page_comments_btn', 'class'=>'c_bg1 c_border1'))
         . Xml::openElement('span', array('class'=>'fa fa-comment-o'))
         . Xml::closeElement('span')
         . Xml::closeElement('button')
         . Xml::input('c', 40, false, array('type'=>'text', 'id'=>'in_page_comments_box', 'maxlength'=>$wgInPageCommentsMaxLetters, 'placeholder'=>InPageCommentsUtil::message($wgInPageCommentsFormMessage)))
         . Xml::closeElement('form');
  }

  public static function renderResources(&$out, &$skin) {
    global $wgInPageCommentsUrlFontAwesome;
    $out->addModules('ext.InPageComments');
    $out->addStyle($wgInPageCommentsUrlFontAwesome, 'all');
  }

  public static function setParserHook(&$parser) {
    global $wgInPageCommentsTag;
    $parser->setHook($wgInPageCommentsTag, array('InPageCommentsHooks', 'renderCommentForm'));
  }
}
