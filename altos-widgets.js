jQuery(document).ready (function($)
	{
		var altosWidgets_slideShow = function($this)
			{
				var slideData = $this.attr ('data-s'), slides = slideData.split ('|');
				var currentSlide = Number($this.attr ('currentSlide')), totalSlides = Number($this.attr ('totalSlides'));
				var nextSlide = (currentSlide + 1 > totalSlides) ? 1 : currentSlide + 1;
				/**/
				$this.fadeOut ('fast', function() /* Transition. */
					{
						var $this = $(this);
						$this.attr ('src', slides[nextSlide - 1]), $this.fadeIn ('fast');
						$this.attr ('currentSlide', nextSlide);
					});
			};
		/**/
		$('img.altos-charts-slide-show').each (function()
			{
				var $this = $(this), slideData = $this.attr ('data-s'), i = 0, preload = null, slides = [];
				/**/
				if (slideData && (slides = slideData.split ('|')).length > 1)
					{
						$this.attr ('currentSlide', 1);
						$this.attr ('totalSlides', slides.length);
						/**/
						for (i = 0; i < slides.length; i++) preload = new Image (), preload.src = slides[i];
						/**/
						setInterval(function() /* 3 second interval. */
							{
								altosWidgets_slideShow($this);
							}, 3000);
					}
			});
	});