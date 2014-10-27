<?php
if(!defined('MEDIAWIKI')) die;
class InPageComments {
  public function __construct(&$api) {
    try {
      mb_regex_encoding('UTF-8');
      $this->init();
      $this->blockCheck();
      $this->parse($api->getVal('c'), $api->getVal('t'));
      $this->spamCheck();
      $this->validate();
      $this->update();
    } catch(Exception $e) {
      InPageCommentsUtil::debug(__FUNCTION__, 'exception', $e);
      $this->errors = array('in-page-comments-error-unknown');
    }
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getValue($key) {
    return $this->values[$key];
  }

  public function isBlocked() {
    return $this->block;
  }

  public function isErrored() {
    return !empty($this->errors);
  }

  public function isSpam() {
    return $this->spam;
  }

  private $block, $errors, $spam, $values;

  private function blockCheck() {
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    global $wgInPageCommentsDenyBlockedUser, $wgUser;
    if($wgInPageCommentsDenyBlockedUser && $wgUser->isBlocked()) {
      $this->block = true;
      $this->error('in-page-comments-error-user-blocked');
    }
    InPageCommentsUtil::debug(__FUNCTION__, '$this->block', $this->block);
  }

  private function error($e) {
    array_push($this->errors, $e);
  }

  private function formatComment($c) {
    global $wgInPageCommentsAnonDisplay, $wgUser;
    $u = ($wgUser->isAnon() ? InPageCommentsUtil::message($wgInPageCommentsAnonDisplay) : '[[User:' . $wgUser->getName() .'|' . $wgUser->getName() . ']]');
    $t = '<span class="time">' . InPageCommentsUtil::time() . '</span>';
    return InPageCommentsUtil::message('in-page-comments-format-comment', $c, $u, $t);
  }

  private function init() {
    $this->block  = false;
    $this->errors = array();
    $this->spam   = false;
    $this->values = array();
  }

  private function parse($c, $t) {
    if($this->isBlocked()) return;
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    $this->parseComment($c);
    InPageCommentsUtil::debug(__FUNCTION__, 'raw comment',    $this->values['r']);
    InPageCommentsUtil::debug(__FUNCTION__, 'parsed comment', $this->values['c']);
    $this->parseTitleAndArticle($t);
    InPageCommentsUtil::debug(__FUNCTION__, 'title',          $this->values['t']);
  }

  private function parseComment($c) {
    $this->values['r'] = $c;
    $c = InPageCommentsUtil::strTrim($c);
    if($c) {
      $c = InPageCommentsUtil::strRemoveControlChar($c);
      $c = InPageCommentsUtil::strEscapeSpecialChar($c);
      $this->values['c'] = $c;
    } else {
      $this->error('in-page-comments-error-comment-empty');
    }
  }

  private function parseTitleAndArticle($t) {
    $t = InPageCommentsUtil::strTrim($t);
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

  private function spamCheck() {
    if($this->isBlocked()) return;
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    global $wgInPageCommentsSpamCheckEncoding, $wgInPageCommentsSpamCheckReferer, $wgInPageCommentsSpamCheckUrlCount;
    if($wgInPageCommentsSpamCheckEncoding) {
      InPageCommentsUtil::debug(__FUNCTION__, 'encoding', $_SERVER['HTTP_ACCEPT_ENCODING']);
      if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
        $this->spam = true;
      }
    }
    if($wgInPageCommentsSpamCheckReferer) {
      InPageCommentsUtil::debug(__FUNCTION__, 'referer', $_SERVER['HTTP_REFERER']);
      InPageCommentsUtil::debug(__FUNCTION__, 'server', $_SERVER['SERVER_NAME']);
      if(strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) === false) {
        $this->spam = true;
      }
    }
    if($wgInPageCommentsSpamCheckUrlCount) {
      $i = preg_match_all('{https?://}', $this->values['r']);
      InPageCommentsUtil::debug(__FUNCTION__, 'url-count', $i);
      if($i > $wgInPageCommentsSpamCheckUrlCount) {
        $this->spam = true;
      }
    }
    InPageCommentsUtil::debug(__FUNCTION__, '$this->spam', $this->spam);
  }

  private function update() {
    if($this->isBlocked() || $this->isErrored() || $this->isSpam()) return;
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    global $wgInPageCommentsTag;
    if(preg_match("/\A(.*<${wgInPageCommentsTag}[^>]*>)(.*)\z/s", $this->values['a']->getContent(), $m)) {
      $this->values['a']->doEdit(($m[1] . $this->formatComment($this->values['c']) . $m[2]), InPageCommentsUtil::message('in-page-comments-update-summary'));
    } else {
      $this->error('in-page-comments-error-tag-absence');
    }
  }

  private function validate() {
    if($this->isBlocked() || $this->isSpam()) return;
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    InPageCommentsUtil::debug(__FUNCTION__, '$this->errors', $this->errors);
    $this->validateComment();
    $this->validateTitle();
  }

  private function validateComment() {
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    global $wgInPageCommentsMaxLetters;
    if($this->values['c']) {
      if(mb_strlen($this->values['r']) > $wgInPageCommentsMaxLetters) {
        $this->error(array('in-page-comments-error-comment-toolong', $wgInPageCommentsMaxLetters));
      }
    } else {
      $this->error('in-page-comments-error-comment-empty');
    }
    InPageCommentsUtil::debug(__FUNCTION__, '$this->errors', $this->errors);
  }

  private function validateTitle() {
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    global $wgInPageCommentsDenyProtectedPage;
    if($wgInPageCommentsDenyProtectedPage && $this->values['t'] && $this->values['t']->isProtected('edit')) {
      $this->error('in-page-comments-error-page-protected');
    }
    InPageCommentsUtil::debug(__FUNCTION__, '$this->errors', $this->errors);
  }
}
