<?php
if(!defined('MEDIAWIKI')) die;
class InPageCommentsApi extends ApiBase {
  const COOKIE_PREFIX   = 'InPageComments';
  const COOKIE_DURATION = 1200;

  public function execute() {
    $ipc = new InPageComments($this->getMain());
    if($ipc->isSpam()) return;
    if($ipc->isErrored()) {
      $this->setErrors($ipc);
    } else {
      $this->setCookie($ipc);
    }
  }

  public function getDescription() {
    return 'Post comments via InPageComments extension';
  }

  public function getAllowedParams() {
    return array_merge(parent::getAllowedParams(), array('c'=>array(ApiBase::PARAM_TYPE => 'string', ApiBase::PARAM_REQUIRED => true), 't'=>array(ApiBase::PARAM_TYPE => 'string', ApiBase::PARAM_REQUIRED => true)));
  }

  public function getParamDescription() {
    return array_merge(parent::getParamDescription(), array('c'=>'Comment', 't'=>'Title'));
  }

  private function setErrors(&$ipc, $html=array()) {
    array_push($html, '<ul>');
    foreach(array_unique($ipc->getErrors()) as $e) {
      if(is_array($e)) {
        array_push($html, '<li>' . call_user_func_array('InPageCommentsUtil::message', $e) . '</li>');
      } else {
        array_push($html, '<li>' . InPageCommentsUtil::message($e) . '</li>');
      }
    }
    array_push($html, '</ul>');
    $r = $this->getResult();
    $r->addValue(null, 'error', true);
    $r->addValue(null, 'message', join('', $html));
  }

  private function setCookie(&$ipc) {
    InPageCommentsUtil::debug(__FUNCTION__, 'begin');
    $key = self::COOKIE_PREFIX . $ipc->getValue('a')->getLatest();
    $res = $this->getRequest()->response();
    $res->setcookie($key, '1', time() + self::COOKIE_DURATION, array('path'=>'/', 'httpOnly'=>false));
  }
}
