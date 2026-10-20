<section class="popular-listing-section">
			<div class="container">

				<!-- Section Title Start -->
				<div class="section-title-2" data-aos="fade-up" data-aos-duration="500">
					<div class="d-flex align-items-center justify-content-center">
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
						<h2>Discover <span> Popular</span> Listing</h2>
						<span class="title-square bg-primary"></span><span class="title-square bg-secondary"></span>
					</div>
					<p>Ready to buy your dream home? find it here</p>
				</div>
				<!-- Section Title End -->

				<ul class="nav nav-pills listing-nav-2" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link active" data-bs-toggle="tab" href="#listing-1" role="tab" aria-controls="listing-1" aria-selected="true"> 
							For Rent
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" data-bs-toggle="tab" href="#listing-2" role="tab" aria-controls="listing-2" aria-selected="false" tabindex="-1">
							For Sale
						</a>
					</li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane fade active show" id="listing-1" role="tabpanel"><div class="row">@forelse($rentProperties->take(6) as $property)<div class="col-xl-4 col-md-6"><x-home-two-property-card :property="$property"/></div>@empty<div class="col-12"><div class="alert alert-light">No properties in this category yet. <a href="{{ route('contact') }}">Contact us</a> with your requirements.</div></div>@endforelse</div></div>

					<div class="tab-pane fade " id="listing-2" role="tabpanel"><div class="row">@forelse($saleProperties->take(6) as $property)<div class="col-xl-4 col-md-6"><x-home-two-property-card :property="$property"/></div>@empty<div class="col-12"><div class="alert alert-light">No properties in this category yet. <a href="{{ route('contact') }}">Contact us</a> with your requirements.</div></div>@endforelse</div></div>

				</div>
			</div>
		</section>
