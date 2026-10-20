/*
Author       : Dreamstechnologies
Template Name: Dreams Estate - Bootstrap Template
*/

(function () {
    "use strict";

	// Slide animation helpers (vanilla replacement for jQuery slideUp/slideDown/slideToggle)

	function slideUp(el, duration) {
		duration = duration || 300;
		if (!el) return;
		el.style.height = el.offsetHeight + 'px';
		el.style.overflow = 'hidden';
		el.style.transitionProperty = 'height, margin, padding';
		el.style.transitionDuration = duration + 'ms';
		el.offsetHeight; // force reflow
		requestAnimationFrame(function () {
			el.style.height = '0px';
			el.style.paddingTop = '0px';
			el.style.paddingBottom = '0px';
			el.style.marginTop = '0px';
			el.style.marginBottom = '0px';
		});
		window.setTimeout(function () {
			el.style.display = 'none';
			el.style.removeProperty('height');
			el.style.removeProperty('padding-top');
			el.style.removeProperty('padding-bottom');
			el.style.removeProperty('margin-top');
			el.style.removeProperty('margin-bottom');
			el.style.removeProperty('overflow');
			el.style.removeProperty('transition-duration');
			el.style.removeProperty('transition-property');
		}, duration);
	}

	function slideDown(el, duration) {
		duration = duration || 300;
		if (!el) return;
		el.style.removeProperty('display');
		var display = window.getComputedStyle(el).display;
		if (display === 'none') display = 'block';
		el.style.display = display;
		var height = el.offsetHeight;
		el.style.overflow = 'hidden';
		el.style.height = '0px';
		el.style.paddingTop = '0px';
		el.style.paddingBottom = '0px';
		el.style.marginTop = '0px';
		el.style.marginBottom = '0px';
		el.offsetHeight; // force reflow
		el.style.transitionProperty = 'height, margin, padding';
		el.style.transitionDuration = duration + 'ms';
		requestAnimationFrame(function () {
			el.style.height = height + 'px';
			el.style.removeProperty('padding-top');
			el.style.removeProperty('padding-bottom');
			el.style.removeProperty('margin-top');
			el.style.removeProperty('margin-bottom');
		});
		window.setTimeout(function () {
			el.style.removeProperty('height');
			el.style.removeProperty('overflow');
			el.style.removeProperty('transition-duration');
			el.style.removeProperty('transition-property');
		}, duration);
	}

	function slideToggle(el, duration) {
		if (!el) return;
		if (window.getComputedStyle(el).display === 'none') {
			slideDown(el, duration);
		} else {
			slideUp(el, duration);
		}
	}

	// Choices init helper (reused for dynamically added .select elements)

	function initChoices(scope) {
		if (!window.Choices) return;
		var root = scope || document;
		root.querySelectorAll('.select').forEach(function (el) {
			if (el.choicesInstance || el.hasAttribute('data-choices') || el.hasAttribute('data-toggle')) return;
			el.choicesInstance = new Choices(el, {
				shouldSort: false,
				searchEnabled: false,
				itemSelectText: ''
			});
		});
	}

    // Sidebar

	// Delegated listener (bound once) instead of re-attaching a direct
	// listener to every link on each resize, which stacked duplicate
	// handlers and made the open/close toggle cancel itself out.
	document.addEventListener('click', function (e) {
		if (window.innerWidth > 991) return;

		var link = e.target.closest('.main-nav a');
		if (!link) return;

		var parentLi = link.parentElement;

		if (parentLi && parentLi.classList.contains('has-submenu')) {
			e.preventDefault();

			var submenu = link.nextElementSibling;

			if (!link.classList.contains('submenu')) {
				// Close other open submenus (but not the one we're about to
				// open, or slideUp/slideDown fight over the same element)
				link.closest('ul').querySelectorAll('ul').forEach(function (ul) {
					if (ul === submenu || submenu.contains(ul)) return;
					slideUp(ul, 350);
				});
				link.closest('ul').querySelectorAll('a').forEach(function (a) {
					if (a !== link) a.classList.remove('submenu');
				});

				// Open current submenu
				slideDown(submenu, 350);
				link.classList.add('submenu');
			} else {
				link.classList.remove('submenu');
				slideUp(submenu, 350);
			}
		}
	});

	// Sticky Header

	window.addEventListener('scroll', function () {
		var header = document.querySelector('header');
		if (!header) return;
		if (window.scrollY > 130) {
			header.classList.add('fixed');
		} else {
			header.classList.remove('fixed');
		}
	});

	// Mobile menu sidebar overlay

	document.body.insertAdjacentHTML('beforeend', '<div class="sidebar-overlay"></div>');

	document.addEventListener('click', function (e) {
		if (e.target.closest('#mobile_btn')) {
			var mainWrapper = document.querySelector('.main-wrapper');
			if (mainWrapper) mainWrapper.classList.toggle('slide-nav');
			document.querySelectorAll('.sidebar-overlay').forEach(function (el) {
				el.classList.toggle('opened');
			});
			document.documentElement.classList.toggle('menu-opened');
			e.preventDefault();
			return false;
		}

		var overlay = e.target.closest('.sidebar-overlay');
		if (overlay) {
			document.documentElement.classList.remove('menu-opened');
			overlay.classList.remove('opened');
			var mainWrapper2 = document.querySelector('.main-wrapper');
			if (mainWrapper2) mainWrapper2.classList.remove('slide-nav');
		}

		if (e.target.closest('#menu_close')) {
			document.documentElement.classList.remove('menu-opened');
			document.querySelectorAll('.sidebar-overlay').forEach(function (el) {
				el.classList.remove('opened');
			});
			var mainWrapper3 = document.querySelector('.main-wrapper');
			if (mainWrapper3) mainWrapper3.classList.remove('slide-nav');
		}
	});

	// theiaStickySidebar (vanilla sticky positioning)

	if (window.innerWidth > 767) {
		document.querySelectorAll('.theiaStickySidebar').forEach(function (el) {
			el.style.position = 'sticky';
			el.style.top = '30px';
		});
	}

	// View all Show hide One

	if (document.querySelectorAll('.more-menu').length > 0) {
		document.querySelectorAll('.more-menu').forEach(function (el) {
			el.style.display = 'none';
		});
		document.querySelectorAll('.viewall-button').forEach(function (btn) {
			btn.addEventListener('click', function () {
				this.textContent = this.textContent === 'Less' ? 'See More' : 'Less';
				document.querySelectorAll('.more-menu').forEach(function (el) {
					slideToggle(el, 900);
				});
			});
		});
	}

	if (document.querySelectorAll('.more-menu1').length > 0) {
		document.querySelectorAll('.more-menu1').forEach(function (el) {
			el.style.display = 'none';
		});
		document.querySelectorAll('.viewall1-button').forEach(function (btn) {
			btn.addEventListener('click', function () {
				this.textContent = this.textContent === 'Less' ? 'See More' : 'Less';
				document.querySelectorAll('.more-menu1').forEach(function (el) {
					slideToggle(el, 900);
				});
			});
		});
	}

    // Select 2

	initChoices();

	// Map

	window.addEventListener('scroll', function () {
		document.querySelectorAll('.map-right').forEach(function (el) {
			if (window.scrollY > 150) {
				el.classList.add('map-top');
			} else {
				el.classList.remove('map-top');
			}
		});
	});

	// Listing Slider

	if (document.querySelectorAll('.listing-slider').length > 0) {
		var swiper = new Swiper(".listing-slider", {
		slidesPerView: 1,
		spaceBetween: 24,
		keyboard: {
			enabled: true,
		},
		pagination: {
			el: ".swiper-pagination",
			clickable: true,
		},
		navigation: {
			nextEl: ".swiper-button-next",
			prevEl: ".swiper-button-prev",
		},
		loop: true,
		breakpoints: {
			768: {
				slidesPerView: 2,
			},
			992: {
				slidesPerView: 1,
			},
			1300: {
				slidesPerView: 2,
			},
		}
		});
	}

	// Review Slider

	if (document.querySelectorAll('.review-slider').length > 0) {
		var reviewSwiper = new Swiper(".review-slider", {
		slidesPerView: 1,
		spaceBetween: 24,
		keyboard: {
			enabled: true,
		},
		pagination: {
			el: ".swiper-pagination",
			clickable: true,
		},
		navigation: {
			nextEl: ".swiper-button-next",
			prevEl: ".swiper-button-prev",
		},
		loop: false,
		breakpoints: {
			768: {
				slidesPerView: 2,
			},
			992: {
				slidesPerView: 3,
			},
			1300: {
				slidesPerView: 4,
			},
		}
		});
	}

	// Partners Slider

	if (document.querySelectorAll('.partner-slider-two').length > 0) {
		var partnerSwiperTwo = new Swiper(".partner-slider-two", {
		slidesPerView: 1,
		spaceBetween: 24,
		keyboard: {
			enabled: true,
		},
		loop: true,
		autoplay: {
			delay: 3000,
			disableOnInteraction: false
		},
		breakpoints: {
			768: {
				slidesPerView: 3,
			},
			992: {
				slidesPerView: 5,
			},
			1300: {
				slidesPerView: 6,
			},
		}
		});
	}

	// Partners Slider

	if (document.querySelectorAll('.partner-slider').length > 0) {
		var partnerSwiper = new Swiper(".partner-slider", {
		slidesPerView: 1,
		spaceBetween: 24,
		keyboard: {
			enabled: true,
		},
		loop: true,
		autoplay: {
			delay: 3000,
			disableOnInteraction: false
		},
		breakpoints: {
			576: {
				slidesPerView: 2,
			},
			768: {
				slidesPerView: 3,
			},
			992: {
				slidesPerView: 5,
			},
			1300: {
				slidesPerView: 7,
			},
		}
		});
	}

	// Select Favourite

	document.querySelectorAll('.favourite').forEach(function (el) {
		el.addEventListener('click', function () {
			this.classList.toggle('selected');
		});
	});

	// Modal

	if (document.querySelector('.topbar-search')) {
		document.querySelector('.topbar-search').addEventListener('click', function () {
			document.body.classList.add('modal-search');
		});
	}

	if (document.querySelector('.close')) {
		document.querySelector('.close').addEventListener('click', function () {
			document.body.classList.remove('modal-search');
		});
	}

	// Custom Country Code Selector

	if (typeof window.intlTelInput === 'function' && document.querySelector('#phone')) {
		var input = document.querySelector("#phone");
		window.intlTelInput(input, {
			utilsScript: "assets/plugins/intltelinput/js/utils.js",
		});
	}

	if (typeof window.intlTelInput === 'function' && document.querySelector('#phone2')) {
		var input2 = document.querySelector("#phone2");
		window.intlTelInput(input2, {
			utilsScript: "assets/plugins/intltelinput/js/utils.js",
		});
	}

	if (typeof window.intlTelInput === 'function' && document.querySelector('#phone3')) {
		var input3 = document.querySelector("#phone3");
		window.intlTelInput(input3, {
			utilsScript: "assets/plugins/intltelinput/js/utils.js",
		});
	}

	/// cart count
	document.addEventListener("DOMContentLoaded", function () {
	if (!document.getElementById("cart-wrap")) return; // Exit if not on the right page

	const btnCovers = document.querySelectorAll(".btn-cover");

	btnCovers.forEach((container) => {
		const input = container.querySelector(".quantity-input");
		const plusBtn = container.querySelector(".add-btn");
		const minusBtn = container.querySelector(".minus-btn");

		plusBtn.addEventListener("click", () => {
			let currentValue = parseInt(input.value) || 0;
			input.value = currentValue + 1;
		});

		minusBtn.addEventListener("click", () => {
			let currentValue = parseInt(input.value) || 0;
			if (currentValue > 1) {
				input.value = currentValue - 1;
			}
		});
	});

	});

	// Datetimepicker
	if (window.flatpickr && document.querySelectorAll('.datetimepicker').length > 0) {
		flatpickr('.datetimepicker', {
			dateFormat: 'd/m/Y',
			allowInput: true
		});
	}

	// timepicker
	if (window.flatpickr && document.querySelectorAll('.timepicker').length > 0) {
		flatpickr('.timepicker', {
			enableTime: true,
			noCalendar: true,
			dateFormat: 'h:i K',
			time_24hr: false,
			allowInput: true
		});
	}

	// Add new Scedule
	document.querySelectorAll('.add-floor-plan-btn').forEach(function (btn) {
		btn.addEventListener('click', function (e) {

			var addcontent = `
				<!-- start row -->
				<div class="row add-count">

					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label">Property Name</label>
							<input type="text" class="form-control">
						</div>
					</div> <!-- end col -->

					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label">Property Type</label>
							<select class="select">
								<option>Select</option>
								<option>Buy</option>
								<option>Sale</option>
							</select>
						</div>
					</div> <!-- end col -->

					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label">Property Category</label>
							<select class="select">
								<option>Select</option>
								<option>Apartment</option>
								<option>Villa</option>
								<option>Condo</option>
								<option>Residency</option>
							</select>
						</div>
					</div> <!-- end col -->

					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label">Currency Type</label>
							<select class="select">
								<option>Select</option>
								<option>Cash</option>
								<option>Bank Transfer</option>
							</select>
						</div>
					</div> <!-- end col -->

					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label">Sale Price</label>
							<input type="text" class="form-control">
						</div>
					</div> <!-- end col -->

					<div class="col-sm-6">
						<div class="mb-3">
							<label class="form-label">Offer Price</label>
							<input type="text" class="form-control">
						</div>
					</div> <!-- end col -->

					<div class="col-sm-12">
						<div class="mb-3">
							<label class="form-label">Description of Property</label>
							<textarea class="form-control" rows="3">Description</textarea>
						</div>
					</div> <!-- end col -->

					<div class="col-sm-12">
						<div class="mb-3">
							<label class="form-label">Photo</label>
							<div class="file-uploader">
								<input type="file" class="form-control">
								<a href="#" class="input-file">
									<span class="browse-btn">Browse Files</span>
									<span class="browse-text">3 Photos Selected</span>
								</a>
							</div>
						</div>
					</div> <!-- end col -->

					<div class="col-sm-12">
						<div class="d-flex justify-content-end mb-3">
							<a href="javascript:void(0);" class="trash delete-icon"><i class="material-icons-outlined">delete</i></a>
						</div>
					</div>
				</div>
				<!-- end row -->`;

			var addFloorList = document.querySelector('.add-floor-list');
			if (addFloorList) {
				addFloorList.insertAdjacentHTML('beforeend', addcontent);
				initChoices(addFloorList);
			}

			e.preventDefault();
			return false;

		});
	});

	document.addEventListener('click', function (e) {
		var trash = e.target.closest('.add-floor-list .trash');
		if (trash) {
			var addCount = trash.closest('.add-count');
			if (addCount) addCount.remove();
			e.preventDefault();
			return false;
		}
	});

	// JQuery CounterUp
	const counters = document.querySelectorAll('.counter-up');

	if (counters.length > 0) {
		counters.forEach(counter => {
			// Extract the target number (12000) directly from the text inside the span
			const target = +counter.innerText.replace(/[^\d.]/g, '');
			const duration = 1500; // 1.5 seconds matching your original jQuery code
			const frameRate = 15;  // 15ms delay matching your original jQuery code
			const totalSteps = duration / frameRate;
			const increment = target / totalSteps;

			let currentCount = 0;

			const updateCounter = () => {
				currentCount += increment;

				if (currentCount < target) {
					// Formats numbers automatically (e.g., 12,000) while it counts up
					counter.innerText = Math.ceil(currentCount).toLocaleString();
					setTimeout(updateCounter, frameRate);
				} else {
					// Guarantees the animation stops exactly at 12,000
					counter.innerText = target.toLocaleString();
				}
			};

			// Reset text to 0 before starting the count-up effect
			counter.innerText = "0";
			updateCounter();
		});
	}


	// Horizontal Slide

	document.addEventListener("DOMContentLoaded", function () {
		const scrollers = document.querySelectorAll(".horizontal-slide");
		scrollers.forEach((scroller) => {
		scroller.setAttribute("data-animated", true);
		const scrollerInner = scroller.querySelector(".slide-list");
		const scrollerContent = Array.from(scrollerInner.children);
		scrollerContent.forEach((item) => {
			const duplicatedItem = item.cloneNode(true);
			duplicatedItem.setAttribute("aria-hidden", true);
			scrollerInner.appendChild(duplicatedItem);
		});
		});
	});

		document.querySelectorAll('input[type="radio"][name="pay-tab"]').forEach(radio => {
    radio.addEventListener('change', function () {
      const target = this.nextElementSibling.getAttribute('data-bs-target');
      const tabs = document.querySelectorAll('.tab-pane');
      tabs.forEach(tab => {
        tab.classList.remove('show', 'active');
      });
      document.querySelector(target).classList.add('show', 'active');
    });
  });

  //   toggle-passwords

	document.addEventListener('click', function (e) {
		if (e.target.closest('.toggle-passwords')) {
			e.target.closest('.toggle-passwords').classList.toggle('fa-eye');
			e.target.closest('.toggle-passwords').classList.toggle('fa-eye-slash');
			var input = document.querySelector('.pass-inputs');
			if (input) {
				input.type = input.type === 'password' ? 'text' : 'password';
			}
		}

		if (e.target.closest('.toggle-password')) {
			e.target.closest('.toggle-password').classList.toggle('fa-eye');
			e.target.closest('.toggle-password').classList.toggle('fa-eye-slash');
			var input2 = document.querySelector('.pass-input');
			if (input2) {
				input2.type = input2.type === 'password' ? 'text' : 'password';
			}
		}

		if (e.target.closest('.toggle-passworda')) {
			e.target.closest('.toggle-passworda').classList.toggle('fa-eye');
			e.target.closest('.toggle-passworda').classList.toggle('fa-eye-slash');
			var input3 = document.querySelector('.pass-inputa');
			if (input3) {
				input3.type = input3.type === 'password' ? 'text' : 'password';
			}
		}
	});

	// Faq card
	document.addEventListener("DOMContentLoaded", function () {
    // Select all collapsible elements
    document.querySelectorAll(".card-collapse").forEach(function (collapseEl) {
        collapseEl.addEventListener("show.bs.collapse", function () {
            // Add 'active' class to parent .faq-card when expanded
            this.closest(".faq-card").classList.add("active");
        });

        collapseEl.addEventListener("hide.bs.collapse", function () {
            // Remove 'active' class when collapsed
            this.closest(".faq-card").classList.remove("active");
        });
    });
});


	// Service Slider (main + synced thumbnail nav)
	document.querySelectorAll('.service-slider-card').forEach(function (card) {
		const mainEl = card.querySelector('.service-slider');
		const navEl = card.querySelector('.slider-nav-thumbnails');
		if (!mainEl || mainEl.swiper) return;

		const thumbsSwiper = navEl ? new Swiper(navEl, {
			slidesPerView: 6,
			spaceBetween: 10,
			loop: true,
			watchSlidesProgress: true,
			navigation: {
				nextEl: navEl.querySelector('.swiper-button-next'),
				prevEl: navEl.querySelector('.swiper-button-prev')
			},
			breakpoints: {
				0: { slidesPerView: 2 },
				400: { slidesPerView: 2 },
				576: { slidesPerView: 2 },
				768: { slidesPerView: 3 },
				992: { slidesPerView: 4 },
				1200: { slidesPerView: 5 }
			}
		}) : null;

		new Swiper(mainEl, {
			slidesPerView: 1,
			loop: true,
			effect: 'fade',
			fadeEffect: { crossFade: true },
			thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined,
			observer: true,
			observeParents: true
		});
	});

	// Gallery Slider
	document.querySelectorAll('.gallery-slider').forEach(function (el) {
		if (el.swiper) return;
		new Swiper(el, {
			slidesPerView: 5,
			spaceBetween: 16,
			loop: true,
			speed: 2000,
			observer: true,
			observeParents: true,
			breakpoints: {
				0: { slidesPerView: 2, spaceBetween: 10 },
				768: { slidesPerView: 2, spaceBetween: 10 },
				992: { slidesPerView: 3, spaceBetween: 12 },
				1300: { slidesPerView: 4, spaceBetween: 16 }
			}
		});
	});

	// Property Slider
	document.querySelectorAll('.property-slider').forEach(function (el) {
		if (el.swiper) return;
		new Swiper(el, {
			slidesPerView: 4,
			spaceBetween: 16,
			loop: true,
			speed: 2000,
			navigation: {
				nextEl: el.querySelector('.swiper-button-next'),
				prevEl: el.querySelector('.swiper-button-prev')
			},
			observer: true,
			observeParents: true,
			breakpoints: {
				0: { slidesPerView: 1, spaceBetween: 10 },
				768: { slidesPerView: 1, spaceBetween: 10 },
				992: { slidesPerView: 3, spaceBetween: 12 },
				1300: { slidesPerView: 4, spaceBetween: 16 }
			}
		});
	});

	// Features Slider
	document.querySelectorAll('.features-slider').forEach(function (el) {
		if (el.swiper) return;
		// Nav buttons may live outside the slider (as siblings inside a
		// shared .position-relative wrapper) rather than inside it.
		var navScope = el.closest('.position-relative') || el;
		new Swiper(el, {
			slidesPerView: 3,
			spaceBetween: 16,
			loop: true,
			speed: 2000,
			navigation: {
				nextEl: navScope.querySelector('.swiper-button-next'),
				prevEl: navScope.querySelector('.swiper-button-prev')
			},
			observer: true,
			observeParents: true,
			breakpoints: {
				0: { slidesPerView: 1, spaceBetween: 10 },
				768: { slidesPerView: 1, spaceBetween: 10 },
				992: { slidesPerView: 2, spaceBetween: 12 },
				1300: { slidesPerView: 3, spaceBetween: 16 }
			}
		});
	});

	// Partners Slider
	document.querySelectorAll('.partners-slider').forEach(function (el) {
		if (el.swiper) return;
		new Swiper(el, {
			slidesPerView: 6,
			spaceBetween: 16,
			loop: true,
			speed: 2000,
			autoplay: { delay: 0, disableOnInteraction: false },
			observer: true,
			observeParents: true,
			breakpoints: {
				0: { slidesPerView: 2, spaceBetween: 10 },
				768: { slidesPerView: 2, spaceBetween: 10 },
				992: { slidesPerView: 3, spaceBetween: 12 },
				1300: { slidesPerView: 4, spaceBetween: 16 }
			}
		});
	});

	// Testimonials Slider
	document.querySelectorAll('.testimonials-slider').forEach(function (el) {
		if (el.swiper) return;
		// Nav buttons may live outside the slider (as siblings inside a
		// shared .position-relative wrapper) rather than inside it.
		var navScope = el.closest('.position-relative') || el;
		new Swiper(el, {
			slidesPerView: 4,
			spaceBetween: 16,
			loop: true,
			speed: 2000,
			navigation: {
				nextEl: navScope.querySelector('.swiper-button-next'),
				prevEl: navScope.querySelector('.swiper-button-prev')
			},
			observer: true,
			observeParents: true,
			breakpoints: {
				0: { slidesPerView: 1, spaceBetween: 10 },
				768: { slidesPerView: 1, spaceBetween: 10 },
				992: { slidesPerView: 2, spaceBetween: 12 },
				1300: { slidesPerView: 2, spaceBetween: 16 }
			}
		});
	});

	// Cities Slider
	document.querySelectorAll('.cities-slider').forEach(function (el) {
		if (el.swiper) return;
		new Swiper(el, {
			slidesPerView: 3,
			spaceBetween: 16,
			loop: true,
			speed: 2000,
			pagination: {
				el: el.querySelector('.swiper-pagination'),
				clickable: true
			},
			observer: true,
			observeParents: true,
			breakpoints: {
				0: { slidesPerView: 1, spaceBetween: 10 },
				768: { slidesPerView: 1, spaceBetween: 10 },
				992: { slidesPerView: 2, spaceBetween: 12 },
				1300: { slidesPerView: 2, spaceBetween: 16 }
			}
		});
	});

	// AOS Animation

	if (document.querySelectorAll('.main-wrapper .aos').length > 0) {
	    AOS.init({
		  duration: 1200,
		  once: true,
		});
	}


	// GLightbox
	if (window.GLightbox && document.querySelectorAll('.glightbox').length > 0) {
		GLightbox({
			selector: '.glightbox',
			touchNavigation: true,
			loop: true,
			zoomable: true
		});
	}

	// blog carousal
	document.addEventListener('DOMContentLoaded', function () {
		var blogCarouselEl = document.querySelector('.blog-carousel');
		if (blogCarouselEl && !blogCarouselEl.swiper) {
			new Swiper(blogCarouselEl, {
				slidesPerView: 3,
				spaceBetween: 24,
				loop: true,
				speed: 300,
				autoplay: { delay: 2000, disableOnInteraction: false },
				navigation: {
					nextEl: '.blog-carousel-next',
					prevEl: '.blog-carousel-prev'
				},
				observer: true,
				observeParents: true,
				breakpoints: {
					0: { slidesPerView: 1, spaceBetween: 16 },
					480: { slidesPerView: 1, spaceBetween: 16 },
					600: { slidesPerView: 1, spaceBetween: 16 },
					1024: { slidesPerView: 2, spaceBetween: 20 },
					1200: { slidesPerView: 3, spaceBetween: 24 }
				}
			});
		}
	});

// heart fill
  document.querySelectorAll('.favourite i').forEach(icon => {
    icon.addEventListener('click', function () {
      if (this.textContent === 'favorite_border') {
        this.textContent = 'favorite';
        this.classList.add('filled');
      } else {
        this.textContent = 'favorite_border';
        this.classList.remove('filled');
      }
    });
  });

  // Locate the range_03 container element
	const range03 = document.getElementById("range_03");

	if (range03) {
		noUiSlider.create(range03, {
			// Sets starting values for both handles [from, to]
			start: [200, 800],
			
			// Fills the space color between handles
			connect: true, 
			
			// Adds visible values right above the handles
			tooltips: [true, true], 
			
			// Sets the scale constraints [min, max]
			range: { 
				'min': 0, 
				'max': 1000 
			},
			
			// Customizes formatting to attach the "$" prefix
			format: {
				to: function (value) {
					return '$' + Math.round(value);
				},
				from: function (value) {
					return value.replace('$', '');
				}
			},
		});
	}


})(); // End: Dreams Estate Template Main JS
