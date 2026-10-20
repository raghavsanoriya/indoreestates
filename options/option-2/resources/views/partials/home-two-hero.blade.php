<section class="home-banner-two">

			<div>
				<div class="banner-img-right" data-aos="fade-down" data-aos-duration="1000">
					<img src="{{ asset('assets/img/section-bg/banner-bg-02.png') }}" alt="">
				</div>
				<div>
					<img src="{{ asset('assets/img/bg/banner-round-bg.svg') }}" class="banner-round" alt="">
				</div>
				<div>
					<img src="{{ asset('assets/img/bg/banner-shape.svg') }}" class="banner-shape" alt="">
				</div>
			</div>

			<div class="container">

				<!-- start row -->
				<div class="row">

					<div class="col-lg-5">
						<div class="banner-users d-flex align-items-center flex-wrap gap-2 mb-3"><div class="avatar-list-stacked">@foreach($locationProjects->take(4) as $project)<span class="avatar avatar-md rounded-circle border-0"><img src="{{ $project->coverUrl() }}" class="img-fluid rounded-circle" alt="{{ $project->title }}"></span>@endforeach</div><div><div class="d-flex align-items-center mb-1"><h6 class="mb-0 me-2 text-white fw-semibold fs-14">{{ $total }} Indore Properties</h6><i class="material-icons-outlined text-warning">location_on</i></div><p class="mb-0 text-white fs-13">Properties for sale and rent in Indore</p></div></div>
						<div class="banner-title aos" data-aos="fade-up">
							<h1>Find your <span>Next Home</span> Away from Home.</h1>
							<p>Explore homes, land and commercial properties in Indore. Find project details and contact us for current pricing.</p>
						</div>
						<div class="d-flex align-items-center">
							<a href="{{ route('properties.index',['purpose'=>'sale']) }}" class="btn btn-primary btn-lg d-inline-flex align-items-center me-3"><i class="material-icons-outlined me-2">shopping_basket</i>Buy Property</a>
							<a href="{{ route('properties.index',['purpose'=>'rent']) }}" class="btn btn-white btn-lg d-inline-flex align-items-center"><i class="material-icons-outlined me-2">king_bed</i>Rent Property</a>
						</div>
					</div> <!-- end col -->

				</div>
				<!-- end row -->

			</div>
		</section>
