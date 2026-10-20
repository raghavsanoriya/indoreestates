<section class="elementor-section elementor-top-section elementor-element elementor-element-d30cad4 elementor-section-stretched elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="d30cad4" data-element_type="section">
						<div class="elementor-container elementor-column-gap-no">
					<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-8ab0a71" data-id="8ab0a71" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<section class="elementor-section elementor-inner-section elementor-element elementor-element-d0d2313 elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="d0d2313" data-element_type="section">
						<div class="elementor-container elementor-column-gap-extended">
					<div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-7277940" data-id="7277940" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-f1be2bc elementor-widget__width-initial  elementor-widget elementor-widget-heading" data-id="f1be2bc" data-element_type="widget" data-widget_type="heading.default">
				<div class="elementor-widget-container">
			<h1 class="elementor-heading-title elementor-size-default">Easy Way to Find a
Perfect Property</h1>		</div>
				</div>
				<div class="elementor-element elementor-element-847d62b  elementor-widget elementor-widget-text-editor" data-id="847d62b" data-element_type="widget" data-widget_type="text-editor.default">
				<div class="elementor-widget-container">
							Explore homes, land and commercial properties in Indore.						</div>
				</div>
					</div>
		</div>
				<div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-7ae3fca" data-id="7ae3fca" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-c2c8dda elementor-widget__width-auto  elementor-widget elementor-widget-apus_element_popup_video" data-id="c2c8dda" data-element_type="widget" data-widget_type="apus_element_popup_video.default"><div class="elementor-widget-container"><div class="widget-video"><div class="video-wrapper-inner"><a class="d-flex align-items-center style2 o3-hero-explore" href="{{ route('properties.index') }}"><h2 class="title-video">Explore Properties</h2><span class="popup-video-inner d-flex align-items-center justify-content-center"><i class="flaticon-up-right-arrow" aria-hidden="true"></i></span></a></div></div></div></div>
				<div class="elementor-element elementor-element-d055504 elementor-widget__width-auto elementor-absolute elementor-hidden-mobile  elementor-widget elementor-widget-image" data-id="d055504" data-element_type="widget" data-widget_type="image.default">
				<div class="elementor-widget-container">
															<img decoding="async" width="140" height="120" src="/source/demoapus1.com/homez/wp-content/uploads/2022/11/round-text.png" class="attachment-full size-full wp-image-606" alt="">															</div>
				</div>
				<div class="elementor-element elementor-element-047b0b9 elementor-widget__width-auto elementor-absolute elementor-hidden-tablet elementor-hidden-mobile  elementor-widget elementor-widget-image" data-id="047b0b9" data-element_type="widget" data-widget_type="image.default">
				<div class="elementor-widget-container">
															<img decoding="async" width="160" height="103" src="/source/demoapus1.com/homez/wp-content/uploads/2022/11/h45.png" class="attachment-full size-full wp-image-613" alt="">															</div>
				</div>
					</div>
		</div>
							</div>
		</section>
				<section class="elementor-section elementor-inner-section elementor-element elementor-element-ffe9029 elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="ffe9029" data-element_type="section">
						<div class="elementor-container elementor-column-gap-extended">
					<div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-7be5e56" data-id="7be5e56" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-b63ddec  elementor-widget elementor-widget-apus_element_realestate_search_form_tabs" data-id="b63ddec" data-element_type="widget" data-widget_type="apus_element_realestate_search_form_tabs.default"><div class="elementor-widget-container"><div class="widget-property-search-form"><form method="get" action="{{ route('properties.index') }}" class="form-search filter-listing-form style1 horizontal o3-hero-search">
<ul class="tab-list nav-tabs nav">@foreach([''=>'All','sale'=>'For Sale','rent'=>'For Rent'] as $value=>$label)<li><label><input type="radio" name="purpose" value="{{ $value }}" @checked($loop->first)><span>{{ $label }}</span></label></li>@endforeach</ul>
<div class="search-form-inner position-relative"><div class="main-inner clearfix"><div class="content-main-inner"><div class="row align-items-center list-fileds"><div class="col-12 col-md-8"><div class="form-group form-group-title"><div class="form-group-inner inner"><i class="flaticon-home" aria-hidden="true"></i><input type="search" name="location" class="form-control" placeholder="Enter an address or neighbourhood in Indore" aria-label="Location / Address"></div></div></div><div class="col-12 col-md-4 form-group-search"><div class="d-flex align-items-center no-text"><button class="advance-search-btn o3-advanced-button" type="button" data-bs-toggle="collapse" data-bs-target="#home-advanced" aria-expanded="false" aria-controls="home-advanced"><i class="flaticon-settings" aria-hidden="true"></i>Advanced</button><button class="btn-submit btn btn-theme no-text" aria-label="Search properties"><i class="flaticon-search pre" aria-hidden="true"></i></button></div></div></div></div></div>
<div class="collapse o3-advanced-fields" id="home-advanced"><div class="row g-3"><div class="col-md-4"><label for="home-type">Property type</label><select class="form-control" id="home-type" name="type"><option value="">All types</option>@foreach(['Apartment','Villa','House','Studio','Office','Land'] as $type)<option>{{ $type }}</option>@endforeach</select></div><div class="col-md-4"><label for="home-min">Min price (₹)</label><input class="form-control" id="home-min" name="min_price" type="number" min="0"></div><div class="col-md-4"><label for="home-max">Max price (₹)</label><input class="form-control" id="home-max" name="max_price" type="number" min="0"></div></div></div></div></form></div></div></div>
				<div class="elementor-element elementor-element-7bb3eeb elementor-widget__width-initial  elementor-widget elementor-widget-apus_element_realestate_property_types" data-id="7bb3eeb" data-element_type="widget" data-widget_type="apus_element_realestate_property_types.default">
				<div class="elementor-widget-container">
			            <div class="widget-property-types nofullscreen  sp_small">
                
                
                                    <div class="row">
                                                                            <div class="col-lg-3 col-md-3 col-6 list-item ">
                                <a class="type-banner-inner position-relative style2 " href="{{ route('properties.index',['type'=>'House']) }}">
                                    
                                    
                                    
                                        <div class="info-type">
                                                                                            <div class="type-icon d-flex align-items-center justify-content-center"><i class="flaticon-home"></i></div>
                                                                                        <div class="inner">
                                                                                                    <h4 class="title">
                                                        Houses                                                    </h4>
                                                                                                                                            </div>
                                        </div>
                                </a>
                            </div>
                                                    <div class="col-lg-3 col-md-3 col-6 list-item ">
                                <a class="type-banner-inner position-relative style2 " href="{{ route('properties.index',['type'=>'Apartment']) }}">
                                    
                                    
                                    
                                        <div class="info-type">
                                                                                            <div class="type-icon d-flex align-items-center justify-content-center"><i class="flaticon-corporation"></i></div>
                                                                                        <div class="inner">
                                                                                                    <h4 class="title">
                                                        Apartments                                                    </h4>
                                                                                                                                            </div>
                                        </div>
                                </a>
                            </div>
                                                    <div class="col-lg-3 col-md-3 col-6 list-item ">
                                <a class="type-banner-inner position-relative style2 " href="{{ route('properties.index',['type'=>'Office']) }}">
                                    
                                    
                                    
                                        <div class="info-type">
                                                                                            <div class="type-icon d-flex align-items-center justify-content-center"><i class="flaticon-network"></i></div>
                                                                                        <div class="inner">
                                                                                                    <h4 class="title">
                                                        Office                                                    </h4>
                                                                                                                                            </div>
                                        </div>
                                </a>
                            </div>
                                                    <div class="col-lg-3 col-md-3 col-6 list-item ">
                                <a class="type-banner-inner position-relative style2 " href="{{ route('properties.index',['type'=>'Villa']) }}">
                                    
                                    
                                    
                                        <div class="info-type">
                                                                                            <div class="type-icon d-flex align-items-center justify-content-center"><i class="flaticon-garden"></i></div>
                                                                                        <div class="inner">
                                                                                                    <h4 class="title">
                                                        Villa                                                    </h4>
                                                                                                                                            </div>
                                        </div>
                                </a>
                            </div>
                                            </div>
                            </div>
        		</div>
				</div>
					</div>
		</div>
				<div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-1a40337 elementor-hidden-tablet elementor-hidden-mobile" data-id="1a40337" data-element_type="column">
			<div class="elementor-widget-wrap">
									</div>
		</div>
							</div>
		</section>
					</div>
		</div>
							</div>
		</section>
