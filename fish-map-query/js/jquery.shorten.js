/*
 * Usage
Step 1: Include the jQuery plugin in your HTML
    <script type="text/javascript" src="jquery.shorten.js"></script>

Step 2: Add the code to shorten any DIV content. In below example we are shortening DIV with class “comment”.
    <div class="comment">
        This is a long comment text.
        This is a long comment text.
        This is a long comment text.
        This is a long comment text. This is a long comment text.
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $(".comment").shorten();
        });
    </script>

You may want to pass the parameters to shorten() method and override the default ones.
    $(".comment").shorten({
        "showChars" : 200
    });

    $(".comment").shorten({
        "showChars" : 150,
        "moreText"  : "See More",
    });

    $(".comment").shorten({
        "showChars" : 50,
        "moreText"  : "See More",
        "lessText"  : "Less",
    });
 */
jQuery.fn.shorten = function(settings) {
  var config = {
    showChars : 100,
    ellipsesText : "...",
    moreText : "more",
    lessText : "less"
  };

  if (settings) {
    $.extend(config, settings);
  }

  $('.morelink').live('click', function() {
    var $this = $(this);
    if ($this.hasClass('less')) {
      $this.removeClass('less');
      $this.html(config.moreText);
    } else {
      $this.addClass('less');
      $this.html(config.lessText);
    }
    $this.parent().prev().toggle();
    $this.prev().toggle();
    return false;
  });

  return this.each(function() {
    var $this = $(this);

    var content = $this.html();
    if (content.length > config.showChars) {
      var c = content.substr(0, config.showChars);
      var h = content.substr(config.showChars , content.length - config.showChars);
      var html = c + '<span class="moreellipses">' + config.ellipsesText + '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="javascript://nop/" class="morelink">' + config.moreText + '</a></span>';
      $this.html(html);
      $(".morecontent span").hide();
    }
  });
};