/*!
 * jQuery ImageMagicalizer Plugin
 * https://github.com/andyexeter/jquery-imagemagicalizer
 */
(function($) {
    $.fn.ImageMagicalize = function(options) {
        var settings = $.extend({
            hoverActivation: false,
            imagePath: '',
            imageWidth: 0,
            imageHeight: 0
        }, options);

        return this.each(function() {
            var $container = $(this);
            var $beforeImg = $container.find('.before');
            var $afterImg = $container.find('.after');
            var $handle = $container.find('.handle');
            
            if (!$beforeImg.length || !$afterImg.length) {
                console.error('Before/After images not found');
                return;
            }

            var containerWidth = $container.width();
            var isDragging = false;

            // 초기 위치 (50%)
            updatePosition(containerWidth / 2);

            function updatePosition(position) {
                var percentage = (position / containerWidth) * 100;
                percentage = Math.max(0, Math.min(100, percentage));
                
                $afterImg.css('clip', 'rect(0px, ' + position + 'px, ' + $container.height() + 'px, 0px)');
                $handle.css('left', percentage + '%');
            }

            // 마우스 이벤트
            $container.on('mousedown', function(e) {
                isDragging = true;
                updatePosition(e.pageX - $container.offset().left);
                e.preventDefault();
            });

            $(document).on('mousemove', function(e) {
                if (isDragging) {
                    updatePosition(e.pageX - $container.offset().left);
                }
            });

            $(document).on('mouseup', function() {
                isDragging = false;
            });

            // 터치 이벤트 (모바일)
            $container.on('touchstart', function(e) {
                isDragging = true;
                var touch = e.originalEvent.touches[0];
                updatePosition(touch.pageX - $container.offset().left);
                e.preventDefault();
            });

            $(document).on('touchmove', function(e) {
                if (isDragging) {
                    var touch = e.originalEvent.touches[0];
                    updatePosition(touch.pageX - $container.offset().left);
                }
            });

            $(document).on('touchend', function() {
                isDragging = false;
            });

            // Hover 활성화
            if (settings.hoverActivation) {
                $container.on('mousemove', function(e) {
                    if (!isDragging) {
                        updatePosition(e.pageX - $container.offset().left);
                    }
                });
            }

            // 윈도우 리사이즈
            $(window).on('resize', function() {
                containerWidth = $container.width();
                updatePosition(containerWidth / 2);
            });
        });
    };
})(jQuery);