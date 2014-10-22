<?php
if(!defined('MEDIAWIKI')) die;
class InPageCommentsUtil {
  public static $control_chars = array("\a", "\e", "\f", "\n", "\r", "\t", "\v");
  public static $special_chars = array('!'=>'&#33;', '"'=>'&#34;', '#'=>'&#35;', '$'=>'&#36;', '%'=>'&#37;', '&'=>'&#38;', "'"=>'&#39;', '('=>'&#40;', ')'=>'&#41;', '*'=>'&#42;', '+'=>'&#43;', ','=>'&#44;', '-'=>'&#45;', '.'=>'&#46;', '/'=>'&#47;', ':'=>'&#58;', ';'=>'&#59;', '<'=>'&#60;', '='=>'&#61;', '>'=>'&#62;', '?'=>'&#63;', '@'=>'&#64;', '['=>'&#91;', '\\'=>'&#92;', ']'=>'&#93;', '^'=>'&#94;', '_'=>'&#95;', '`'=>'&#96;', '{'=>'&#123;', '|'=>'&#124;', '}'=>'&#125;', '~'=>'&#126;');

  public static function debug($f, $k, $v='') {
    global $wgInPageCommentsDebugMode;
    if($wgInPageCommentsDebugMode) {
      try {
        if($k == 'begin') {
          wfDebugLog('InPageComments', substr("-> [${f}] " . str_repeat("=", 60), 0, 60));
        } else {
          if($v instanceof Exception) {
            wfDebugLog('InPageComments', str_repeat("=", 60) . "\n" . $v->__toString());
            wfDebugLog('InPageComments', str_repeat("=", 60));
            return;
          }
          if($v instanceof Title) {
            wfDebugLog('InPageComments', substr('title->dbkey'     . str_repeat(' ', 20), 0, 20) . ': ' . $v->getDBKey());
            wfDebugLog('InPageComments', substr('title->namespace' . str_repeat(' ', 20), 0, 20) . ': ' . $v->getNamespace());
            return;
          }
          if(is_array($v) && empty($v)) $v = 'array()';
          $v = var_export($v, true);
          wfDebugLog('InPageComments', substr($k . str_repeat(' ', 20), 0, 20) . ": $v");
        }
      } catch(Exception $e) {
        try {
          wfDebugLog('InPageComments', 'An exception was thrown while logging.');
          wfDebugLog('InPageComments', str_repeat("=", 60) . "\n" . $e->__toString());
          wfDebugLog('InPageComments', str_repeat("=", 60));
        } catch(Exception $e) {}
      }
    }
  }

  public static function formatComment($c) {
    global $wgInPageCommentsAnonDisplay, $wgUser;
    $u = ($wgUser->isAnon() ? self::message($wgInPageCommentsAnonDisplay) : '[[User:' . $wgUser->getName() .'|' . $wgUser->getName() . ']]');
    $t = '<span class="time">' . self::time() . '</span>';
    return self::message('in-page-comments-format-comment', $c, $u, $t);
  }

  public static function message() {
    $a = func_get_args();
    $m = call_user_func_array('wfMessage', $a);
    if($m->isBlank()) {
      return $a[0];
    } else {
      return $m->text();
    }
  }

  public static function strEscapeSpecialChar($s) {
    return strtr($s, self::$special_chars);
  }

  public static function strRemoveControlChar($s) {
    return str_replace(self::$control_chars, '', $s);
  }

  public static function strTrim($s) {
    return mb_ereg_replace('(\A[\0\s]+|[\0\s]+\z)', '', $s);
  }

  public static function time() {
    global $wgInPageCommentsTimeFormat, $wgLocaltimezone, $wgUser;
    $o = explode('|', $wgUser->getOption('timecorrection'), 3);
    if($o[0] == 'ZoneInfo') {
      try {$tz = new DateTimeZone($o[2]);} catch(Exception $e) {}
    }
    if(!$tz) {
      if(isset($wgLocaltimezone)) {
        try {$tz = new DateTimeZone($wgLocaltimezone);} catch(Exception $e) {}
      } else {
        try {$tz = new DateTimeZone(date_default_timezone_get());} catch(Exception $e) {}
      }
    }
    if(!$tz) {
      $tz = new DateTimeZone('UTC');
    }
    return (new DateTime)->setTimezone($tz)->format(self::message('in-page-comments-format-time'));
  }
}
