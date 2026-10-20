<section class="about-us-section-2">
			<div class="container">

				<!-- start row -->
				<div class="row align-items-center gy-4">

					<div class="col-lg-6">

						<!-- Section Title Start -->
						<div class="title-head" data-aos="fade-up" data-aos-duration="500">
							<span class="badge bg-secondary mb-2">About Us</span>
							<h2 class="mb-2">We make property discovery simple, transparent, and stress-free.</h2>
							<p>Explore properties and developer projects in Indore. Search by your requirements, save favourites and send private enquiries. Imported projects include their source details so you can confirm current availability and pricing.</p>
							<div class="d-flex align-items-center">
								<a href="{{ route('listings.create') }}" class="btn btn-dark btn-lg me-3">Start Post Your Property</a>
								<a href="{{ route('contact') }}" class="btn btn-primary btn-lg">Contact Us</a>
							</div>
						</div>
						<!-- Section Title End -->

					</div> <!-- end col -->

					<div class="col-lg-6">
						<div class="position-relative" data-aos="fade-up" data-aos-duration="1000">
							<div><img src="{{ asset('assets/img/section-bg/section-bg-01.png') }}" class="img-fluid" alt=""></div>
							<div class="position-absolute end-0 top-0">
								<img src="{{ asset('assets/img/bg/line-01.svg') }}" alt="">
							</div>
							<div class="position-absolute start-0 bottom-0">
								<img src="{{ asset('assets/img/bg/line-02.svg') }}" alt="">
							</div>
						</div>
					</div> <!-- end col -->

				</div>
				<!-- end row -->

				<div class="counter-list"><div class="row">@foreach(['Available Properties'=>$total,'Properties for Sale'=>$saleCount,'Properties for Rent'=>$rentCount,'City Served'=>1] as $label=>$value)<div class="col-lg-3 col-sm-6 d-flex" data-aos="fade-up"><div class="counting-item flex-fill"><span class="count-icon"><img src="{{ asset('assets/img/icons/count-0'.([1,2,3,2][$loop->index]).'.svg') }}" alt=""></span><div><h4 class="mb-1"><span>{{ $value }}</span></h4><p class="mb-0">{{ $label }}</p></div></div></div>@endforeach</div></div>
			</div>
		</section>
