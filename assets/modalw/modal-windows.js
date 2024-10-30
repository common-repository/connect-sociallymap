(function($) {
    $(document).ready(function(){
        var saveStyle = $('html').css('overflow');

        $(document).on('click touchstart', '.sm-display-modal', function(e) {
            var self = $(this);

            var href = self.attr('href');

            $('html').css('overflow', 'hidden');

            var iframe = '<div class="sm-iframe-wrapper"><iframe class="modalw-iframe" src="'+href+'" id="modalw-sm-iframe"></iframe></div>';
            var wrapper = '<div class="modalw-wrapper"><div class="modalw-load-wrapper"><p class="modalw-sm-loader"></div></p></div>';

            $('body').append(wrapper+'<div class="modalw-container"><p class="modalw-close">X</p>'+iframe+'</div>');

            $('#modalw-sm-iframe').load(function(){
                $('.modalw-container').css('display', 'block');
                $('.modalw-load-wrapper').remove();

                resizingWindow();

                $(window).resize(function() {
                    resizingWindow();
                });
            });

            e.preventDefault();
            return false;
        });

        $(document).on('click touchstart', '.modalw-close, .modalw-wrapper', function (e) {
            $('.modalw-container').fadeOut('slow', function() {
                $(this).remove();
            });
            $('.modalw-wrapper').fadeOut('slow', function() {
                $(this).remove();
            });
            $('html').css('overflow', saveStyle);

            e.preventDefault();
            return false;
        });

        function resizingWindow() {
            // Get size of windows
            var widthWindows = $(document).width();

            // Calc size of modal (80% of windows)
            var widthDynamic = widthWindows * 0.8;

            // Set size of modal
            $('.modalw-container').width(widthDynamic);

            // calc margin left
            var marginPicked = (widthWindows * 0.1)/1.3;

            // set margin left to windows
            $('.modalw-container').css('left', marginPicked+'px');
        }
    });
}(jQuery));
