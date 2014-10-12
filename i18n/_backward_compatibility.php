<?php
if(!defined('MEDIAWIKI')) die;
if(!function_exists('wfJsonI18nInPageComments')) {
  function wfJsonI18nInPageComments($cache, $code, &$cachedData) {
    $codeSequence = array_merge(array($code), $cachedData['fallbackSequence']);
    foreach ($codeSequence as $csCode) {
      $fileName = dirname( __FILE__ ) . "/$csCode.json";
      if(is_readable($fileName)) {
        $data = FormatJson::decode(file_get_contents($fileName), true);
        foreach(array_keys($data) as $key) {
          if ($key === '' || $key[0] === '@') {
            unset($data[$key]);
          }
        }
        $cachedData['messages'] = array_merge($data, $cachedData['messages']);
      }
      $cachedData['deps'][] = new FileDependency($fileName);
    }
    return true;
  }
  $GLOBALS['wgHooks']['LocalisationCacheRecache'][] = 'wfJsonI18nInPageComments';
}
