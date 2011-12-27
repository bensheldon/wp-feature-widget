(function($) {
  $(window).load(function() {
    $('#slider').nivoSlider({
      effect:'fade', // Specify sets like: 'fold,fade,sliceDown'
      pauseTime:5000, // How long each slide will show
      controlNav:false, // 1,2,3... navigation
      captionOpacity:1, // Universal caption opacity
    });
  });
})(jQuery);