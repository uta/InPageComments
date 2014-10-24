(function($, mw) {
  'use strict';
  mw.InPageComments = {
    $cookie_prefix: 'InPageComments',
    init: function() {
      this.message();
      this.post();
    },
    message: function() {
      $(document).ready(function() {
        var config = mw.config.get(['wgAction', 'wgCookiePrefix', 'wgCurRevisionId']);
        var cookie = config.wgCookiePrefix + mw.InPageComments.$cookie_prefix + config.wgCurRevisionId;
        if(config.wgAction == 'view' && $.cookie(cookie) == '1') {
          $.cookie(cookie, null, {path:'/'});
          mw.hook('postEdit').fire({message:mw.message('in-page-comments-message-succeeded').text()});
        }
      });
    },
    post: function() {
      $(document).ready(function() {
        var e = $('#in_page_comments_form');
        if(e.length) {
          e.on('submit', mw.InPageComments.postExec);
        }
      });
    },
    postExec: function() {
      var e = $('#in_page_comments_box');
      var q = e.val().trim();
      e.val(q);
      return (q.length != 0);
    }
  };
  mw.InPageComments.init();
})(jQuery, mediaWiki);
