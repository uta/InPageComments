(function($, mw) {
  'use strict';
  $(document).ready(function() {
    var config = mw.config.get(['wgAction', 'wgCookiePrefix', 'wgCurRevisionId']);
    var cookie = config.wgCookiePrefix + 'InPageComments' + config.wgCurRevisionId;
    if(config.wgAction === 'view' && $.cookie(cookie) === '1') {
      $.cookie(cookie, null, {path:'/'});
      mw.hook('postEdit').fire({message:mw.message('in-page-comments-message-succeeded').text()});
    }
  });
})(jQuery, mediaWiki);
