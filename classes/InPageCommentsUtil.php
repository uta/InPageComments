<?php
if(!defined('MEDIAWIKI')) die;
class InPageCommentsUtil {
  const EXTENSION = 'InPageComments';
  public static $control_chars = array("\a", "\e", "\f", "\n", "\r", "\t", "\v");
  public static $special_chars = array('!'=>'&#33;', '"'=>'&#34;', '#'=>'&#35;', '$'=>'&#36;', '%'=>'&#37;', '&'=>'&#38;', "'"=>'&#39;', '('=>'&#40;', ')'=>'&#41;', '*'=>'&#42;', '+'=>'&#43;', ','=>'&#44;', '-'=>'&#45;', '.'=>'&#46;', '/'=>'&#47;', ':'=>'&#58;', ';'=>'&#59;', '<'=>'&#60;', '='=>'&#61;', '>'=>'&#62;', '?'=>'&#63;', '@'=>'&#64;', '['=>'&#91;', '\\'=>'&#92;', ']'=>'&#93;', '^'=>'&#94;', '_'=>'&#95;', '`'=>'&#96;', '{'=>'&#123;', '|'=>'&#124;', '}'=>'&#125;', '~'=>'&#126;');

  public static function debug($k, $v='') {
    global $wgDebugLogFile;
    if($wgDebugLogFile) {
      try {
        if($k == 'begin') {
          $trace  = debug_backtrace();
          $caller = $trace[1]['function'];
          $args   = $trace[1]['args'];
          wfDebugLog(self::EXTENSION, substr("==[${caller}]" . str_repeat("=", 60), 0, 60));
          foreach($args as $i => $arg) {
            self::debug("args[$i]", $arg);
          }
        } else {
          if($v instanceof Exception) {
            wfDebugLog(self::EXTENSION, str_repeat("=", 60) . "\n" . $v->__toString());
            wfDebugLog(self::EXTENSION, str_repeat("=", 60));
            return;
          }
          if($v instanceof Title) {
            wfDebugLog(self::EXTENSION, substr('title->dbkey'     . str_repeat(' ', 20), 0, 20) . ': ' . $v->getDBKey());
            wfDebugLog(self::EXTENSION, substr('title->namespace' . str_repeat(' ', 20), 0, 20) . ': ' . $v->getNamespace());
            return;
          }
          if(is_array($v))    $v = empty($v) ? 'array()' : var_export($v, true);
          if(is_bool($v))     $v = var_export($v, true);
          if(is_null($v))     $v = var_export($v, true);
          if(is_numeric($v))  $v = (string)$v;
          if(is_object($v))   $v = 'instance of ' . get_class($v);
          if(is_string($v)) {
            wfDebugLog(self::EXTENSION, substr($k . str_repeat(' ', 20), 0, 20) . ": $v");
          }
        }
      } catch(Exception $e) {
        try {
          wfDebugLog(self::EXTENSION, 'An exception was thrown while logging.');
          wfDebugLog(self::EXTENSION, str_repeat("=", 60) . "\n" . $e->__toString());
          wfDebugLog(self::EXTENSION, str_repeat("=", 60));
        } catch(Exception $e) {}
      }
    }
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
