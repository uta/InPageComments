<?php
if(!defined('MEDIAWIKI')) die;
class InPageCommentsHooks {
  public static function renderCommentForm($text, $params=array(), $parser=null, $frame=false) {
    global $wgInPageCommentsFormMessage, $wgInPageCommentsFormSubmit, $wgInPageCommentsMaxLetters;
    return Xml::openElement('form', array('action'=>SpecialPage::getTitleFor('InPageComments')->getLocalURL(), 'method'=>'post', 'id'=>'in_page_comments_form'))
         . Xml::input('t', false, $parser->mTitle->getDBKey(), array('type'=>'hidden'))
         . Xml::input('c', 40, false, array('type'=>'text', 'maxlength'=>$wgInPageCommentsMaxLetters, 'placeholder'=>InPageCommentsUtil::message($wgInPageCommentsFormMessage)))
         . Xml::submitButton(InPageCommentsUtil::message($wgInPageCommentsFormSubmit))
         . Xml::closeElement('form');
  }

  public static function renderResources(&$out, &$skin) {
    $out->addModules('ext.InPageComments');
  }

  public static function setParserHook(&$parser) {
    global $wgInPageCommentsTag;
    $parser->setHook($wgInPageCommentsTag, array('InPageCommentsHooks', 'renderCommentForm'));
  }
}
