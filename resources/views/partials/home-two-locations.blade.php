<section class="feature-location-section">
			<div class="container">

				<!-- Section Title Start -->
				<div class="section-title-2" data-aos="fade-up" data-aos-duration="500">
					<div class="d-flex align-items-center justify-content-center">
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
						<h2>Discover <span> Featured</span> Location</h2>
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
					</div>
					<p>Ready to buy your dream home? find it here</p>
				</div>
				<!-- Section Title End -->

				<!-- end row -->
				<div class="row gy-4">@foreach($locationProjects->take(4) as $property)<div class="col-lg-3 col-sm-6"><div class="location-item-two"><div class="location-img"><a href="{{ route('properties.show',$property) }}"><img src="{{ $property->coverUrl() }}" class="img-fluid" alt="{{ $property->title }}"></a><div class="position-absolute top-0 end-0 p-3 z-1"><span class="badge bg-light text-dark">Indore</span></div><h5 class="position-absolute start-0 bottom-0 text-white z-1 p-3">{{ $property->neighborhood ?: 'Indore' }}<span class="fs-14 d-block">{{ $property->title }}</span></h5></div></div></div>@endforeach</div>
				<!-- end row -->

				<div class="text-center pt-3">
					<a href="{{ route('properties.index',['location'=>'Indore']) }}" class="btn btn-dark d-inline-flex align-items-center">Explore Indore<i class="material-icons-outlined ms-1">north_east</i></a>
				</div>

			</div>
		</section>
