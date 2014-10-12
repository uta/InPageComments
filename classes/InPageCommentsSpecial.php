<?php
if(!defined('MEDIAWIKI')) die;
class InPageCommentsSpecial extends UnlistedSpecialPage {
  const COOKIE_PREFIX   = 'InPageComments';
  const COOKIE_DURATION = 1200;
  private $block, $errors, $spam, $values;

  public function __construct() {
    parent::__construct('InPageComments');
    $this->block  = false;
    $this->errors = array();
    $this->spam   = false;
    $this->values = array();
  }

  public function execute($par) {
    try {
      mb_regex_encoding('UTF-8');
      $this->blockCheck();
      $this->parse();
      $this->spamCheck();
      $this->validate();
      $this->update();
      $this->render();
    } catch(Exception $e) {
      InPageCommentsUtil::debug('blockCheck', 'execute', $e);
      $this->errors = array('in-page-comments-error-unknown');
      $this->renderErrorPage();
    }
  }

  private function blockCheck() {
    InPageCommentsUtil::debug('blockCheck', 'begin');
    global $wgInPageCommentsDenyBlockedUser;
    if($wgInPageCommentsDenyBlockedUser && $this->getUser()->isBlocked()) {
      $this->block = true;
      $this->error('in-page-comments-error-user-blocked');
    }
    InPageCommentsUtil::debug('blockCheck', '$this->block', $this->block);
  }

  private function error($e) {
    array_push($this->errors, $e);
  }

  private function isBlocked() {
    return $this->block;
  }

  private function isErrored() {
    return !empty($this->errors);
  }

  private function isSpam() {
    return $this->spam;
  }

  private function parse() {
    if($this->isBlocked()) return;
    InPageCommentsUtil::debug('parse', 'begin');
    $this->parseComment();
    InPageCommentsUtil::debug('parse', 'raw comment',    $this->values['r']);
    InPageCommentsUtil::debug('parse', 'parsed comment', $this->values['c']);
    $this->parseTitleAndArticle();
    InPageCommentsUtil::debug('parse', 'title',          $this->values['t']);
  }

  private function parseComment() {
    $c = InPageCommentsUtil::strTrim($_POST['c']);
    if($c) {
      $c = InPageCommentsUtil::strRemoveControlChar($c);
      $c = InPageCommentsUtil::strEscapeSpecialChar($c);
    }
    $this->values['c'] = $c;
    $this->values['r'] = $_POST['c'];
  }

  private function parseTitleAndArticle() {
    $t = InPageCommentsUtil::strTrim($_POST['t']);
    if($t) {
      $t = Title::newFromDBkey($t);
      if($t instanceof Title && $t->exists()) {
        $a = new Article($t);
        if($a instanceof Article && $a->exists()) {
          $this->values['a'] = $a;
          $this->values['t'] = $t;
        } else {
          $this->error('in-page-comments-error-page-empty');
        }
      } else {
        $this->error('in-page-comments-error-page-empty');
      }
    } else {
      $this->error('in-page-comments-error-page-empty');
    }
  }

  private function render() {
    InPageCommentsUtil::debug('render', 'begin');
    if($this->isBlocked() || $this->isErrored()) {
      $this->renderErrorPage();
      return;
    }
    if($this->isSpam()) {
      $this->getOutput()->redirect($this->values['t']->getFullURL());
      return;
    }
    $this->setCookie();
    $this->getOutput()->redirect($this->values['t']->getFullURL() . '#in_page_comments_form');
  }

  private function renderErrorPage() {
    $out = $this->getOutput();
    $out->setPageTitle(InPageCommentsUtil::message('in-page-comments-failed-title'));
    $out->addHTML(Xml::openElement('div', array('class'=>'errorbox')));
    foreach(array_unique($this->errors) as $e) {
      if(is_array($e)) {
        call_user_func_array(array($out, 'addWikiMsg'), $e);
      } else {
        $out->addWikiMsg($e);
      }
    }
    $out->addHTML(Xml::closeElement('div'));
    $out->addHTML(Xml::openElement('p', array('id'=>'mw-returnto')));
    $out->addHTML(Xml::openElement('a', array('href'=>'javascript:history.back();')));
    $out->addHTML('Back');
    $out->addHTML(Xml::closeElement('a'));
    $out->addHTML(Xml::closeElement('p'));
  }

  private function setCookie() {
    InPageCommentsUtil::debug('setCookie', 'begin');
    $key = self::COOKIE_PREFIX . $this->values['a']->getLatest();
    $res = RequestContext::getMain()->getRequest()->response();
    $res->setcookie($key, '1', time() + self::COOKIE_DURATION, array('path'=>'/', 'httpOnly'=>false));
  }

  private function spamCheck() {
    if($this->isBlocked()) return;
    InPageCommentsUtil::debug('spamCheck', 'begin');
    global $wgInPageCommentsSpamCheckEncoding, $wgInPageCommentsSpamCheckReferer, $wgInPageCommentsSpamCheckUrlCount;
    if($wgInPageCommentsSpamCheckEncoding) {
      InPageCommentsUtil::debug('spamCheck', 'encoding', $_SERVER['HTTP_ACCEPT_ENCODING']);
      if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
        $this->spam = true;
      }
    }
    if($wgInPageCommentsSpamCheckReferer) {
      InPageCommentsUtil::debug('spamCheck', 'referer', $_SERVER['HTTP_REFERER']);
      InPageCommentsUtil::debug('spamCheck', 'server', $_SERVER['SERVER_NAME']);
      if(strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) === false) {
        $this->spam = true;
      }
    }
    if($wgInPageCommentsSpamCheckUrlCount) {
      $i = preg_match_all('{https?://}', $this->values['r']);
      InPageCommentsUtil::debug('spamCheck', 'url-count', $i);
      if($i > $wgInPageCommentsSpamCheckUrlCount) {
        $this->spam = true;
      }
    }
    InPageCommentsUtil::debug('spamCheck', '$this->spam', $this->spam);
  }

  private function update() {
    if($this->isBlocked() || $this->isErrored() || $this->isSpam()) return;
    InPageCommentsUtil::debug('update', 'begin');
    global $wgInPageCommentsTag;
    if(preg_match("/\A(.*<${wgInPageCommentsTag}[^>]*>)(.*)\z/s", $this->values['a']->getContent(), $m)) {
      $this->values['a']->doEdit(($m[1] . InPageCommentsUtil::formatComment($this->values['c']) . $m[2]), InPageCommentsUtil::message('in-page-comments-update-summary'));
    } else {
      $this->error('in-page-comments-error-tag-absence');
    }
  }

  private function validate() {
    if($this->isBlocked() || $this->isSpam()) return;
    InPageCommentsUtil::debug('validate', 'begin');
    InPageCommentsUtil::debug('validate', '$this->errors', $this->errors);
    $this->validateComment();
    $this->validateTitle();
  }

  private function validateComment() {
    InPageCommentsUtil::debug('validateComment', 'begin');
    global $wgInPageCommentsMaxLetters;
    if($this->values['c']) {
      if(mb_strlen($this->values['r']) > $wgInPageCommentsMaxLetters) {
        $this->error(array('in-page-comments-error-comment-toolong', $wgInPageCommentsMaxLetters));
      }
    } else {
      $this->error('in-page-comments-error-comment-empty');
    }
    InPageCommentsUtil::debug('validate', '$this->errors', $this->errors);
  }

  private function validateTitle() {
    InPageCommentsUtil::debug('validateTitle', 'begin');
    global $wgInPageCommentsDenyProtectedPage;
    if($wgInPageCommentsDenyProtectedPage && $this->values['t'] && $this->values['t']->isProtected('edit')) {
      $this->error('in-page-comments-error-page-protected');
    }
    InPageCommentsUtil::debug('validate', '$this->errors', $this->errors);
  }
}
