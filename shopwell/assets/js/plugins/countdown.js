(function ($) {
    // PERFORMANCE FIX: Unified countdown timer manager
    // Prevents multiple setInterval timers and uses requestAnimationFrame for better performance
    var CountdownManager = {
        timers: [],
        rafId: null,
        isRunning: false,
        
        add: function($element, expireTime) {
            var timer = {
                element: $element[0],
                $element: $element,
                expireTime: expireTime,
                isActive: true
            };
            
            this.timers.push(timer);
            
            // Store timer reference on element for cleanup
            $element.data('countdown-timer', timer);
            
            if (!this.isRunning) {
                this.start();
            }
        },
        
        remove: function($element) {
            var timer = $element.data('countdown-timer');
            if (timer) {
                timer.isActive = false;
                this.timers = this.timers.filter(function(t) {
                    return t !== timer;
                });
            }
            
            if (this.timers.length === 0) {
                this.stop();
            }
        },
        
        updateClock: function(timer, distance) {
            var days = Math.floor(distance / (60 * 60 * 24));
            var hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
            var minutes = Math.floor((distance % (60 * 60)) / (60));
            var seconds = Math.floor(distance % 60);
            var texts = timer.$element.data('text');

            timer.$element.html(
                '<span class="days timer"><span class="digits">' + days + '</span><span class="text">' + texts.days + '</span><span class="divider">:</span></span>' +
                '<span class="hours timer"><span class="digits">' + (hours < 10 ? '0' : '') + hours + '</span><span class="text">' + texts.hours + '</span><span class="divider">:</span></span>' +
                '<span class="minutes timer"><span class="digits">' + (minutes < 10 ? '0' : '') + minutes + '</span><span class="text">' + texts.minutes + '</span><span class="divider">:</span></span>' +
                '<span class="seconds timer"><span class="digits">' + (seconds < 10 ? '0' : '') + seconds + '</span><span class="text">' + texts.seconds + '</span></span>'
            );
        },
        
        tick: function() {
            var now = Date.now();
            var activeTimers = [];
            
            for (var i = 0; i < this.timers.length; i++) {
                var timer = this.timers[i];
                
                if (!timer.isActive) {
                    continue;
                }
                
                // Check if element still exists in DOM
                if (!document.contains(timer.element)) {
                    timer.isActive = false;
                    continue;
                }
                
                var distance = timer.expireTime - now;
                
                if (distance <= 0) {
                    timer.isActive = false;
                    this.updateClock(timer, 0);
                    continue;
                }
                
                // Only update DOM every second (not every frame)
                var seconds = Math.floor(distance / 1000);
                var lastSeconds = timer.lastSeconds || -1;
                
                if (seconds !== lastSeconds) {
                    this.updateClock(timer, Math.floor(distance / 1000));
                    timer.lastSeconds = seconds;
                }
                
                activeTimers.push(timer);
            }
            
            this.timers = activeTimers;
            
            if (this.timers.length > 0) {
                this.rafId = requestAnimationFrame(this.tick.bind(this));
            } else {
                this.stop();
            }
        },
        
        start: function() {
            if (this.isRunning) {
                return;
            }
            
            this.isRunning = true;
            this.rafId = requestAnimationFrame(this.tick.bind(this));
        },
        
        stop: function() {
            this.isRunning = false;
            if (this.rafId) {
                cancelAnimationFrame(this.rafId);
                this.rafId = null;
            }
        }
    };

    $.fn.shopwell_countdown = function () {
        return this.each(function () {
            var $this = $(this);
            
            // Remove existing timer if present
            CountdownManager.remove($this);
            
            var expireSeconds = $this.data('expire');
            if (!expireSeconds || expireSeconds <= 0) {
                return;
            }
            
            // Calculate expire time in milliseconds
            var expireTime = Date.now() + (expireSeconds * 1000);
            
            // Add to unified timer manager
            CountdownManager.add($this, expireTime);
        });
    };

    /* Init tabs */
    $(function () {
        $('.shopwell-countdown').shopwell_countdown();

        $(document.body).on('shopwell_countdown', function (e, $el) {
            $el.shopwell_countdown();
        });
    });
})(jQuery);