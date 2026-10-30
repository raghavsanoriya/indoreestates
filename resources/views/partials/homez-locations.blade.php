<section class="elementor-section elementor-top-section elementor-element elementor-element-2abeae3 elementor-section-stretched elementor-section-content-bottom elementor-section-boxed elementor-section-height-default elementor-section-height-default " data-id="2abeae3" data-element_type="section">
						<div class="elementor-container elementor-column-gap-extended">
					<div class="elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-213a1a9" data-id="213a1a9" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-1850d5e elementor-widget elementor-widget-heading" data-id="1850d5e" data-element_type="widget" data-widget_type="heading.default">
				<div class="elementor-widget-container">
			<h2 class="elementor-heading-title elementor-size-default">{{ $cms->text('home.locations.title') }}</h2>		</div>
				</div>
				<div class="elementor-element elementor-element-bed9317 elementor-widget elementor-widget-text-editor" data-id="bed9317" data-element_type="widget" data-widget_type="text-editor.default">
				<div class="elementor-widget-container">
							{{ $cms->text('home.locations.subtitle') }}						</div>
				</div>
					</div>
		</div>
				<div class="elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-b3b97ba" data-id="b3b97ba" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-dcabf7a elementor-widget__width-auto elementor-widget elementor-widget-button" data-id="dcabf7a" data-element_type="widget" data-widget_type="button.default">
				<div class="elementor-widget-container">
					<div class="elementor-button-wrapper">
			<a href="{{ route('properties.index',['location'=>'Indore']) }}" class="elementor-button-link elementor-button elementor-size-md" role="button">
						<span class="elementor-button-content-wrapper">
							<span class="elementor-button-icon elementor-align-icon-right">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewbox="0 0 16 17" fill="currentColor"><path d="M15.5553 0.670898H5.77756C5.53189 0.670898 5.3331 0.86969 5.3331 1.11536C5.3331 1.36102 5.53189 1.55982 5.77756 1.55982H14.4824L0.129975 15.9122C-0.0436504 16.0859 -0.0436504 16.3671 0.129975 16.5407C0.216766 16.6275 0.330516 16.6709 0.444225 16.6709C0.557933 16.6709 0.671641 16.6275 0.758475 16.5407L15.1109 2.18827V10.8931C15.1109 11.1388 15.3097 11.3376 15.5553 11.3376C15.801 11.3376 15.9998 11.1388 15.9998 10.8931V1.11536C15.9998 0.86969 15.801 0.670898 15.5553 0.670898Z" fill="currentColor"></path></svg>			</span>
						<span class="elementor-button-text">{{ $cms->text('home.locations.button') }}</span>
		</span>
					</a>
		</div>
				</div>
				</div>
					</div>
		</div>
							</div>
		</section>
<section class="elementor-section elementor-top-section elementor-element elementor-element-85ab539 elementor-section-boxed"><div class="elementor-container"><div class="elementor-column elementor-col-100"><div class="elementor-widget-wrap"><div class="widget-property-locations w-100"><div class="o3-location-grid">@foreach($locationProjects as $property)<div class="item"><a class="item-location position-relative style3" href="{{ route('properties.show',$property) }}"><div class="banner-image"><div class="image-wrapper"><img src="{{ $property->coverUrl() }}" alt="{{ $property->title }}" loading="lazy"></div></div><div class="inner d-flex flex-column"><h4 class="title">{{ $property->neighborhood ?: 'Indore' }}</h4><div class="number">{{ $property->title }}</div><span class="more-details">More Details <i class="flaticon-up-right-arrow" aria-hidden="true"></i></span></div></a></div>@endforeach</div></div></div></div></div></section>
