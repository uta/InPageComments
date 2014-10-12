<?php
if(!defined('MEDIAWIKI')) die;
class InPageCommentsHooks {
  public static function renderCommentForm($text, $params=array(), $parser=null, $frame=false) {
    global $wgInPageCommentsFormMessage, $wgInPageCommentsFormSubmit, $wgInPageCommentsMaxLetters;
    return Xml::openElement('form', array('action'=>SpecialPage::getTitleFor('InPageComments')->getLocalURL(), 'method'=>'post', 'id'=>'in_page_comments_form'))
         . Html::hidden('t', $parser->mTitle->getDBKey())
         . Xml::openElement('span')
         . InPageCommentsUtil::message($wgInPageCommentsFormMessage)
         . Xml::closeElement('span')
         . Xml::input('c', 40, '', array('maxlength'=>$wgInPageCommentsMaxLetters))
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
