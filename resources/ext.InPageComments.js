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
    postExec: function(e) {
      var api, p, v;
      e.preventDefault();
      e.stopPropagation();
      e = $('#in_page_comments_box');
      v = e.val().trim();
      p = {action:'InPageComments', c:v, t:mw.config.get('wgRelevantPageName', mw.config.get('wgPageName'))};
      if(v.length != 0) {
        mw.InPageComments.$temp = v;
        mw.InPageComments.state('posting');
        (new mw.Api()).post(p)
                      .done(function(){location.reload();})
                      .fail(function(code, res) {
                        mw.loader.load(['mediawiki.notification'], null, true);
                        mw.notify($.parseHTML(res.message));
                        mw.InPageComments.state('done');
                      });
      } else {
        e.val(v);
      }
      return false;
    },
    state: function(state) {
      switch(state) {
        case 'done':
          $('#in_page_comments_box').val(this.$temp).removeAttr('readonly').attr('placeholder', mw.message('in-page-comments-form-message').text());
          $('#in_page_comments_btn span').removeClass('fa-spinner').removeClass('fa-spin').addClass('fa-comment-o');
          break;
        case 'posting':
          $('#in_page_comments_box').val('').attr('readonly', 'readonly').attr('placeholder', mw.message('in-page-comments-message-posting').text());
          $('#in_page_comments_btn span').removeClass('fa-comment-o').addClass('fa-spinner').addClass('fa-spin');
          break;
      }
    }
  };
  mw.InPageComments.init();
})(jQuery, mediaWiki);
