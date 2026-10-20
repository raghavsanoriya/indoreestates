<section class="property-type-section">
			<div class="container">

				<!-- Section Title Start -->
				<div class="section-title-2" data-aos="fade-up" data-aos-duration="1000">
					<div class="d-flex align-items-center justify-content-center">
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
						<h2>Recommended <span> Property</span> Types </h2>
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
					</div>
					<p>Discover our top service areas, where quality meets convenience.</p>
				</div>
				<!-- Section Title End -->

				<!-- start row -->
				<div class="row">

					<div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="1000">
						<div class="property-type-item">
							<div class="property-img">
								<a href="{{ route('properties.index',['type'=>'House']) }}"><img src="{{ asset('assets/img/property-type/property-type-01.jpg') }}" class="img-fluid" alt=""></a>
								<a href="{{ route('properties.index',['type'=>'House']) }}" class="overlay-arrow"><i class="material-icons-outlined">north_east</i></a>
							</div>
							<div class="text-center">
								<h5 class="mb-1"><a href="{{ route('properties.index',['type'=>'House']) }}">Houses</a></h5>
								<p class="fs-14 mb-0">{{ $typeCounts['House'] ?? 0 }} Properties Available</p>
							</div>
						</div>
					</div> <!-- end col -->

					<div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="1500">
						<div class="property-type-item">
							<div class="property-img">
								<a href="{{ route('properties.index',['type'=>'Office']) }}"><img src="{{ asset('assets/img/property-type/property-type-02.jpg') }}" class="img-fluid" alt=""></a>
								<a href="{{ route('properties.index',['type'=>'Office']) }}" class="overlay-arrow"><i class="material-icons-outlined">north_east</i></a>
							</div>
							<div class="text-center">
								<h5 class="mb-1"><a href="{{ route('properties.index',['type'=>'Office']) }}">Offices</a></h5>
								<p class="fs-14 mb-0">{{ $typeCounts['Office'] ?? 0 }} Properties Available</p>
							</div>
						</div>
					</div> <!-- end col -->

					<div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="2000">
						<div class="property-type-item">
							<div class="property-img">
								<a href="{{ route('properties.index',['type'=>'Villa']) }}"><img src="{{ asset('assets/img/property-type/property-type-03.jpg') }}" class="img-fluid" alt=""></a>
								<a href="{{ route('properties.index',['type'=>'Villa']) }}" class="overlay-arrow"><i class="material-icons-outlined">north_east</i></a>
							</div>
							<div class="text-center">
								<h5 class="mb-1"><a href="{{ route('properties.index',['type'=>'Villa']) }}">Villas</a></h5>
								<p class="fs-14 mb-0">{{ $typeCounts['Villa'] ?? 0 }} Properties Available</p>
							</div>
						</div>
					</div> <!-- end col -->

					<div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="2500">
						<div class="property-type-item">
							<div class="property-img">
								<a href="{{ route('properties.index',['type'=>'Apartment']) }}"><img src="{{ asset('assets/img/property-type/property-type-04.jpg') }}" class="img-fluid" alt=""></a>
								<a href="{{ route('properties.index',['type'=>'Apartment']) }}" class="overlay-arrow"><i class="material-icons-outlined">north_east</i></a>
							</div>
							<div class="text-center">
								<h5 class="mb-1"><a href="{{ route('properties.index',['type'=>'Apartment']) }}">Apartments</a></h5>
								<p class="fs-14 mb-0">{{ $typeCounts['Apartment'] ?? 0 }} Properties Available</p>
							</div>
						</div>
					</div> <!-- end col -->

				</div>
				<!-- end row -->

				<div class="text-center pt-3">
					<a href="{{ route('properties.index',['purpose'=>'sale']) }}" class="btn btn-dark d-inline-flex align-items-center">View More Type<i class="material-icons-outlined ms-1">north_east</i></a>
				</div>

			</div>
		</section>
