(function ($) {
  "use strict";

  $(function () {

    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = wp.template('wpm-ls');
      $('#wpbody-content .wrap').first().prepend(language_switcher);
    }

  });
})(jQuery, wp);
