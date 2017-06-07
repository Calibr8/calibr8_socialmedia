/**
 * @file
 */

var social_share_width = 520;
var social_share_height = 350;

(function ($) {

  Drupal.behaviors.q8SocialShare = {
    attach: function(context, settings) {

      var $socialshare_menu = $('#sharing-menu');

      $('a', $socialshare_menu).click(function(e) {
        var link = $(this).attr('href');
        if(!link.startsWith('mailto')) {
          e.preventDefault();
          var top = (screen.height / 2) - (social_share_width / 2);
          var left = (screen.width / 2) - (social_share_height / 2);
          window.open(link, 'q8-social-share', 'top=' + top + ',left=' + left + ',toolbar=0,status=0,width=' + social_share_width + ',height=' + social_share_height);
        }
      });

    }
  };

})(jQuery);
