<section class="faq-section-two">
			<div class="container">

				<!-- Section Title Start -->
				<div class="section-title-2">
					<div class="d-flex align-items-center justify-content-center">
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
						<h2>Frequently Asked <span> Questions</span></h2>
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
					</div>
					<p>Quick Answers to Common Questions</p>
				</div>
				<!-- Section Title End -->

				<!-- start row -->
				<div class="row align-items-center gy-4">

					<div class="col-lg-6" data-aos="fade-up">
						<div class="property-sec-img mt-0">
							<div class="row g-3">
								<div class="col-md-6 d-flex">
									<div class="flex-fill">
										<div><img src="{{ asset('assets/img/home-3/property/property-01.jpg') }}" class="img-fluid" alt=""></div>
									</div>
								</div>
								<div class="col-md-6 d-flex">
									<div class="flex-fill">
										<div class="mb-3"><img src="{{ asset('assets/img/home-3/property/property-02.jpg') }}" class="img-fluid" alt=""></div>
										<div><img src="{{ asset('assets/img/home-3/property/property-03.jpg') }}" alt=""></div>
									</div>
								</div>
							</div>
							<div class="rotate-div">
							<div class="img-center-text">
								<h3 class="mb-1 text-white">{{ $total }}</h3>
								<p class="mb-0 fs-14 text-white text-center">Indore <br> Properties</p>
							</div>
							</div>
						</div>
					</div> <!-- end col -->

					<div class="col-lg-6" data-aos="fade-up" data-aos-duration="1500">
						<div class="accordion accordions-items-seperate faq-accordion" id="faq-accordion">@foreach(['Where are the properties located?'=>'Public listings are for Indore only.','Why is a price not shown?'=>'If a price has not been supplied, use Contact us on the listing to request current pricing.','How do I save properties and enquire?'=>'Sign in to save favourites and send private enquiries. Replies appear in your dashboard.','How do I submit a property?'=>'Ask the administrator for agent access, then submit your Indore property through the dashboard for review.','Are online bookings and payments active?'=>'Online bookings and payments are not active. Please contact us to discuss a property.'] as $question=>$answer)<div class="accordion-item"><div class="accordion-header"><button class="accordion-button {{ $loop->first?'':'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#accordion-{{ $loop->index }}" aria-expanded="{{ $loop->first?'true':'false' }}">{{ $question }}</button></div><div id="accordion-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first?'show':'' }}" data-bs-parent="#faq-accordion"><div class="accordion-body"><p class="mb-0">{{ $answer }}</p></div></div></div>@endforeach</div>
					</div> <!-- end col -->

				</div>
				<!-- end row -->

				
			</div>
		</section>
