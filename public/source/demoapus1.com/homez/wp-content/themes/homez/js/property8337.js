(function($) {
    "use strict";
    
    $.extend($.apusThemeCore, {
        /**
         *  Initialize scripts
         */
        property_init: function() {
            var self = this;

            self.select2Init();

            self.searchAjaxInit();
            
            self.submitProperty();

            self.listingDetail();

            self.mortgageCalculatorWidget();
            
            self.filterListingFnc();

            self.userLoginRegister();

            self.listingBtnFilter();

            self.changePaddingTopContent();

            self.dashboardChartInit();
            
            $(window).resize(function(){
                setTimeout(function(){
                    self.changePaddingTopContent();
                }, 50);
            });

            self.agentAgencyLoadMore();

            self.propertyCompare();

            self.propertySavedSearch();

            if ( $('.properties-listing-wrapper.main-items-wrapper, .agents-listing-wrapper.main-items-wrapper, .agencies-listing-wrapper.main-items-wrapper').length ) {
                $(document).on('change', 'form.filter-listing-form input, form.filter-listing-form select', function (e) {
                    var form = $(this).closest('form.filter-listing-form');
                    setTimeout(function(){
                        form.trigger('submit');
                    }, 200);
                });

                $(document).on('submit', 'form.filter-listing-form', function (e) {
                    e.preventDefault();
                    var url = $(this).attr('action');

                    var formData = $(this).find(":input").filter(function(index, element) {
                            return $(element).val() != '';
                        }).serialize();

                    if( url.indexOf('?') != -1 ) {
                        url = url + '&' + formData;
                    } else{
                        url = url + '?' + formData;
                    }
                    
                    self.propertiesGetPage( url );
                    return false;
                });

                // Sort Action
                $(document).on('change', 'form.properties-ordering select.orderby', function(e) {
                    e.preventDefault();
                    $('form.properties-ordering').trigger('submit');
                });
                
                $(document).on('submit', 'form.properties-ordering', function (e) {
                    var url = $(this).attr('action');

                    var formData = $(this).find(":input").filter(function(index, element) {
                            return $(element).val() != '';
                        }).serialize();
                    
                    if( url.indexOf('?') != -1 ) {
                        url = url + '&' + formData;
                    } else{
                        url = url + '?' + formData;
                    }
                    self.propertiesGetPage( url );
                    return false;
                });

                // display mode
                $(document).on('change', 'form.properties-display-mode input', function(e) {
                    e.preventDefault();
                    $('form.properties-display-mode').trigger('submit');
                });

                $(document).on('submit', 'form.properties-display-mode', function (e) {
                    var url = $(this).attr('action');

                    if( url.indexOf('?') != -1 ) {
                        url = url + '&' + $(this).serialize();
                    } else{
                        url = url + '?' + $(this).serialize();
                    }
                    self.propertiesGetPage( url );
                    return false;
                });
            }

            $(document).on('click', '.advance-search-btn:not(.popup)', function(e) {
                e.preventDefault();
                $(this).closest('.search-form-inner').find('.advance-search-wrapper-fields').removeClass('overflow-visible').slideToggle('fast', 'swing', function(){
                    if ( !$(this).hasClass('overflow-visible') ) {
                        $(this).addClass('overflow-visible');
                    }
                });
            });

            $('.advance-search-btn.popup, .click-popup').magnificPopup({
                mainClass: 'apus-mfp-zoom-in',
                type:'inline',
                midClick: true,
                modal: true,
                callbacks: {
                    open: function() {
                        self.layzyLoadImage();
                    }
                }
            });

            $(document).on('click', '.close-advance-popup', function(e) {
                e.preventDefault();
                $.magnificPopup.close();
            });
            $(document).on('click', '.submit-advance-search-btn', function(e) {
                e.preventDefault();
                $.magnificPopup.close();
                var form_id = $(this).data('form_id');
                if ( $(form_id).length ) {
                    $(form_id).trigger('submit');
                }
            });

            // ajax pagination
            if ( $('.ajax-pagination').length ) {
                self.ajaxPaginationLoad();
            }

            // if ( $('.page-template-page-dashboard .sidebar-wrapper:not(.offcanvas-filter-sidebar) > .sidebar-right, .page-template-page-dashboard .sidebar-wrapper:not(.offcanvas-filter-sidebar) > .sidebar-left').length ) {
            //     var ps = new PerfectScrollbar('.page-template-page-dashboard .sidebar-wrapper:not(.offcanvas-filter-sidebar) > .sidebar-right, .page-template-page-dashboard .sidebar-wrapper:not(.offcanvas-filter-sidebar) > .sidebar-left', {
            //         wheelPropagation: true
            //     });
            // }

            self.galleryPropery();

            $('.btn-send-mail').on('click', function(){
                var target = $('form.contact-form-wrapper');
                if (target.length) {
                    $('html,body').animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                    return false;
                }
                return false;
            });
        },
        select2Init: function() {
            // select2
            if ( $.isFunction( $.fn.select2 ) && typeof wp_realestate_select2_opts !== 'undefined' ) {
                var select2_args = wp_realestate_select2_opts;
                select2_args['allowClear']              = true;
                select2_args['minimumResultsForSearch'] = 10;
                
                if ( typeof wp_realestate_select2_opts.language_result !== 'undefined' ) {
                    select2_args['language'] = {
                        noResults: function(){
                            return wp_realestate_select2_opts.language_result;
                        }
                    };
                }
                var filter_select2_args = select2_args;
                
                

                select2_args['allowClear'] = false;
                select2_args['theme'] = 'default';

                var register_select2_args = select2_args;
                register_select2_args['minimumResultsForSearch'] = -1;
                // filter
                
                $('select[name=email_frequency]').select2( select2_args );
                $('.register-form select').select2( register_select2_args );

                // fix for widget search
                if( $('.widget-property-search-form.horizontal select').length ){
                    filter_select2_args.theme = 'default customizer-search';
                }
                 
                if ( $('.layout-type-half-map .widget-property-search-form.horizontal select').length ){
                    filter_select2_args.theme = 'default customizer-search customizer-search-halpmap';
                }
                
                filter_select2_args['allowClear'] = true;
                $('.filter-listing-form select').select2( filter_select2_args );
            }
        },
        searchAjaxInit: function() {
            if ( $.isFunction( $.fn.typeahead ) ) {
                $('.apus-autocompleate-input').each(function(){
                    var $this = $(this);
                    $this.typeahead({
                            'hint': true,
                            'highlight': true,
                            'minLength': 2,
                            'limit': 10
                        }, {
                            name: 'search',
                            source: function (query, processSync, processAsync) {
                                processSync([homez_property_opts.empty_msg]);
                                $this.closest('.twitter-typeahead').addClass('loading');

                                var values = {};
                                $.each($this.closest('form').serializeArray(), function (i, field) {
                                    values[field.name] = field.value;
                                });

                                var ajaxurl = homez_property_opts.ajaxurl;
                                if ( typeof wp_realestate_opts.ajaxurl_endpoint !== 'undefined' ) {
                                    var ajaxurl =  wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'homez_autocomplete_search_properties' );
                                }

                                return $.ajax({
                                    url: ajaxurl,
                                    type: 'GET',
                                    data: {
                                        'search': query,
                                        'action': 'homez_autocomplete_search_properties',
                                        'data': values
                                    },
                                    dataType: 'json',
                                    success: function (json) {
                                        $this.closest('.twitter-typeahead').removeClass('loading');
                                        $this.closest('.has-suggestion').removeClass('active');
                                        return processAsync(json);
                                    }
                                });
                            },
                            templates: {
                                empty : [
                                    '<div class="empty-message">',
                                    homez_property_opts.empty_msg,
                                    '</div>'
                                ].join('\n'),
                                suggestion: Handlebars.compile( homez_property_opts.template )
                            },
                        }
                    );
                    $this.on('typeahead:selected', function (e, data) {
                        e.preventDefault();
                        setTimeout(function(){
                            $('.apus-autocompleate-input').val(data.title);    
                        }, 5);
                        
                        return false;
                    });
                });
            }
        },
        submitProperty: function() {
            $(document).on('click', 'ul.submit-property-heading li a', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                if ( $(href).length ) {
                    $('ul.submit-property-heading li').removeClass('active');
                    $(this).closest('li').addClass('active');
                    $('.before-group-row').removeClass('active');
                    $(href).addClass('active');

                    $( "input" ).trigger( "pxg:simplerefreshmap" );
                }
            });

            $(document).on('click', '.job-submission-previous-btn, .job-submission-next-btn', function(e) {
                e.preventDefault();
                var index = $(this).data('index');
                if ( $('.before-group-row-'+index).length ) {
                    $('.before-group-row').removeClass('active');
                    $('.before-group-row-'+index).addClass('active');

                    $('.submit-property-heading li').removeClass('active');
                    $('.submit-property-heading-'+index).addClass('active');

                    $( "input" ).trigger( "pxg:simplerefreshmap" );
                }
            });
        },
        changePaddingTopContent: function() {
            var admin_bar_h = 0;
            if ( $('#wpadminbar').length ){
                var admin_bar_h = $('#wpadminbar').outerHeight();
            }
            var header_h = 0;
            if ($(window).width() >= 1200) {
                if ( $('#apus-header').length ) {
                    var header_h = $('#apus-header').outerHeight();
                }

                var header_main_content_h = header_h - admin_bar_h;

                $('.inner-dashboard.container-fluid .first-row > .sidebar-wrapper').css({ 'top': header_h + admin_bar_h , 'height': 'calc(100vh - ' + header_h + 'px)' });
                $('body.page-template-page-dashboard #apus-main-content').css({ 'padding-top': header_h });

                $('.tabs-product.sticky-top').css({
                    'top': admin_bar_h
                });

                $('.header_transparent .apus-header').css({
                    'top': admin_bar_h
                });

            } else {
                if ( $('#apus-header-mobile').length ) {
                    var header_h = $('#apus-header-mobile').outerHeight();
                }
                if ( $('#properties-google-maps').is('.fix-map') ) {
                    var header_main_content_h = header_h - admin_bar_h;
                }
                $('body.page-template-page-dashboard #apus-main-content').css({ 'padding-top': 0 });
                $('.inner-dashboard.container-fluid .first-row > .sidebar-wrapper').css({ 'top': header_h , 'height': 'calc(100vh - ' + header_h + 'px)' });

                $('.tabs-product.sticky-top').css({
                    'top': admin_bar_h
                });
            }

            if ( $('#apus-footer').length ) {
                $('body.page-template-page-dashboard').css({ 'margin-bottom': $('#apus-footer').outerHeight() });
            }
            
            if ($('#properties-google-maps').is('.fix-map')) {
                var header_h = header_h + admin_bar_h;

                if ($(window).width() >= 1200) {
                    $('#apus-main-content').css({ 'padding-top': header_h - admin_bar_h });
                } else{
                    $('#apus-main-content').css({ 'padding-top': 0 });
                }

                if ($(window).width() >= 992) {
                    if ( $('.properties-filter-top-half-map3').length ) {
                        var filter_top_h = $('.properties-filter-top-half-map3').outerHeight();

                        $('.properties-filter-top-half-map3').css({ 'top': header_h });

                        $('#main-container').css({ 'padding-top': filter_top_h });
                        header_h = header_h + filter_top_h;
                    }
                    if ( $('.properties-filter-top-half-map').length ) {
                        $('.properties-filter-top-half-map').css({ 'top': header_h });
                    }
                } else {
                    $('.properties-filter-top-half-map3').css({ 'top': 0 });
                    $('#main-container').css({ 'padding-top': 0 });
                }

                $('#properties-google-maps').css({ 'top': header_h, 'height': 'calc(100vh - ' + header_h+ 'px)' });
                
            }

            // fix for header
            $('body.page-template-page-dashboard #apus-header, body.fix-header #apus-header').css({ 'top': admin_bar_h });

            // offcanvas-filter-sidebar 
            $('.offcanvas-filter-sidebar').css({ 'padding-top': header_h + 10 });
        },
        agentAgencyLoadMore: function() {
            $(document).on('click', '.ajax-properties-pagination .apus-loadmore-btn', function(e){
                e.preventDefault();
                var $this = $(this);
                
                $this.addClass('loading');

                var ajaxurl = homez_property_opts.ajaxurl;
                if ( typeof wp_realestate_opts.ajaxurl_endpoint !== 'undefined' ) {
                    var ajaxurl =  wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'homez_get_ajax_properties_load_more' );
                }

                $.ajax({
                    url: ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data: {
                        action: 'homez_get_ajax_properties_load_more',
                        paged: $this.data('paged'),
                        post_id: $this.data('post_id'),
                        type: $this.data('type'),
                    }
                }).done(function(data) {
                    $this.removeClass('loading');
                    $this.closest('.agent-agency-detail-properties').find('.row').append(data.output);
                    $this.data('paged', data.paged);
                    if ( !data.load_more ) {
                        $this.closest('.ajax-properties-pagination').addClass('all-properties-loaded');
                    }
                });
            });

            $(document).on('click', '.ajax-agents-pagination .apus-loadmore-btn', function(e){
                e.preventDefault();
                var $this = $(this);
                    
                $this.addClass('loading');

                var ajaxurl = homez_property_opts.ajaxurl;
                if ( typeof wp_realestate_opts.ajaxurl_endpoint !== 'undefined' ) {
                    var ajaxurl =  wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'homez_get_ajax_agents_load_more' );
                }

                $.ajax({
                    url: ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data: {
                        action: 'homez_get_ajax_agents_load_more',
                        paged: $this.data('paged'),
                        post_id: $this.data('post_id'),
                    }
                }).done(function(data) {
                    $this.removeClass('loading');
                    $this.closest('.agent-detail-agents').find('.row').append(data.output);
                    $this.data('paged', data.paged);
                    if ( !data.load_more ) {
                        $this.closest('.ajax-agents-pagination').addClass('all-properties-loaded');
                    }
                });
            });
        },
        listingDetail: function() {
            var self = this;
            
            var adjustheight = 210;
            $('.show-more-less-wrapper').each(function(){
                var desc_height = $(this).closest('.description-inner').find('.description-inner-wrapper').height();
                if ( desc_height > adjustheight ) {
                    $(this).closest('.description-inner').addClass('show-more');
                    $(this).closest('.description-inner').find('.description-inner-wrapper').css({
                        'height': adjustheight,
                        'overflow': 'hidden',
                    });
                }
                $(this).find('.show-more').on('click', function(){
                    $(this).closest('.description-inner').removeClass('show-more').addClass('show-less');
                    $(this).closest('.description-inner').find('.description-inner-wrapper').css({
                        'height': 'auto',
                        'overflow': 'visible',
                    });
                });
                $(this).find('.show-less').on('click', function(){
                    $(this).closest('.description-inner').removeClass('show-less').addClass('show-more');
                    $(this).closest('.description-inner').find('.description-inner-wrapper').css({
                        'height': adjustheight,
                        'overflow': 'hidden',
                    });
                });
            });

            
            $('.btn-print-property').on('click', function(e){
                e.preventDefault();
                
                var $this = $(this);
                $this.addClass('loading');
                var property_id = $(this).data('property_id');
                var nonce = $(this).data('nonce');

                var ajaxurl = homez_property_opts.ajaxurl;
                if ( typeof wp_realestate_opts.ajaxurl_endpoint !== 'undefined' ) {
                    var ajaxurl =  wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'homez_ajax_print_property' );
                }

                var printWindow = window.open('', 'Print Me', 'width=700 ,height=842');
                $.ajax({
                    url: ajaxurl,
                    type:'POST',
                    dataType: 'html',
                    data: {
                        'property_id': property_id,
                        'nonce': nonce,
                        'action': 'homez_ajax_print_property',
                    }
                }).done(function(data) {
                    $this.removeClass('loading');
                    
                    printWindow.document.write(data);
                    printWindow.document.close();
                    printWindow.focus();

                });

            });

            
            // mortgage
            $('.apus-mortgage-calculator').each(function(){
                self.mortgageCalucaltion($(this));
            });

            $('.btn-mortgage-calculator').on('click', function(){
                var $con_wrapper = $(this).closest('.apus-mortgage-calculator');
                self.mortgageCalucaltion($con_wrapper);
            });

            // schedule
            if ( $('.schedule-a-tour-form-wrapper input[name="date"]').length ) {
                var date_format = $('.schedule-a-tour-form-wrapper input[name="date"]').data('date_format');
                $('.schedule-a-tour-form-wrapper input[name="date"]').datetimepicker({
                    timepicker:false,
                    formatDate:date_format,
                    minDate:new Date(),
                    disabledDates: [new Date()]
                });
            }

            $('form.schedule-a-tour-form-wrapper').on('submit', function(){
                var $this = $(this);
                $('.alert', this).remove();
                $this.addClass('loading');
                $.ajax({
                  url: wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'homez_ajax_send_a_schedule' ),
                  type:'POST',
                  dataType: 'json',
                  data:  $(this).serialize()
                }).done(function(data) {
                     $this.removeClass('loading');
                    if ( data.status ) {
                        $this.prepend( '<div class="alert alert-info">'+data.msg+'</div>' );
                    } else {
                        $this.prepend( '<div class="alert alert-warning">'+data.msg+'</div>' );
                    }
                });
                
                return false;
            });

            // if ($('.content-property-detail .sticky-this').length > 0 ) {
            //     if ($(window).width() > 991) {
            //         $('.content-property-detail .sticky-this').stick_in_parent({
            //             parent: ".property-v-wrapper"
            //         });
            //     }
            // }

            $(document).on('after_nearby_yelp_content', function(e, $this, data) {
                var parent = $this.closest('.property-single-layout');
                if ( parent.hasClass('property-single-v5') ) {

                    parent.find('.isotope-items').each(function(){  
                        var $container = $(this);
                        
                        $container.imagesLoaded( function(){
                            parent.find('.isotope-items').isotope({
                                itemSelector : '.isotope-item',
                                transformsEnabled: true,         // Important for videos
                                masonry: {
                                    columnWidth: $container.data('columnwidth')
                                }
                            }); 
                        });
                    });

                }
            });

            $(document).on('after_nearby_google_place_content', function(e, $this, data) {
                var parent = $this.closest('.property-single-layout');
                if ( parent.hasClass('property-single-v5') ) {

                    parent.find('.isotope-items').each(function(){  
                        var $container = $(this);
                        
                        $container.imagesLoaded( function(){
                            parent.find('.isotope-items').isotope({
                                itemSelector : '.isotope-item',
                                transformsEnabled: true,         // Important for videos
                                masonry: {
                                    columnWidth: $container.data('columnwidth')
                                }
                            }); 
                        });
                    });

                }
            });

            $(document).on('after_walk_score_content', function(e, $this, data) {
                var parent = $this.closest('.property-single-layout');
                if ( parent.hasClass('property-single-v5') ) {

                    parent.find('.isotope-items').each(function(){  
                        var $container = $(this);
                        
                        $container.imagesLoaded( function(){
                            parent.find('.isotope-items').isotope({
                                itemSelector : '.isotope-item',
                                transformsEnabled: true,         // Important for videos
                                masonry: {
                                    columnWidth: $container.data('columnwidth')
                                }
                            }); 
                        });
                    });

                }
            });
        },
        mortgageCalucaltion: function($con_wrapper){
            var self = this;
            var $form_container = $con_wrapper.find('form');

            var total_amount = $form_container.find('.total-amount').val();
            var down_payment = $form_container.find('.down-payment').val();
            var interest_rate = $form_container.find('.interest-rate').val();
            var loan_term = $form_container.find('.loan-terms').val();
            var property_tax = $form_container.find('.property-tax').val();
            var home_insurance = $form_container.find('.home-insurance').val();
            //var pmi = $form_container.find('.property-pmi').val();

            if( isNaN( down_payment = Math.abs( down_payment ) ) ) {
                down_payment = 0;
            }

            if( isNaN( interest_rate = Math.abs( interest_rate ) ) ) {
                interest_rate = 0;
            }

            if( isNaN( loan_term = Math.abs( loan_term ) ) ) {
                loan_term = 0;
            }

            if( isNaN( property_tax = Math.abs( property_tax ) ) ) {
                property_tax = 0;
            }

            if( isNaN( home_insurance = Math.abs( home_insurance ) ) ) {
                home_insurance = 0;
            }

            // if( isNaN( pmi = Math.abs( pmi ) ) ) {
            //     pmi = 0;
            // }

            var load_amount = total_amount - down_payment;
            var qty_payments = loan_term * 12;
            var rate = interest_rate / 100 / 12;

            //Present value interest factor.
            var pvif = Math.pow( 1 + rate, qty_payments );
            var pmt = Math.round( rate / ( pvif - 1 ) * - ( load_amount * pvif ) );

            var interest_in_absolute = -pmt;
            var home_insurance = Math.round( home_insurance / 12 * 100 ) / 100;
            var property_tax = Math.round( property_tax / 12 * 100 ) / 100;
            var payment_total = interest_in_absolute + property_tax + home_insurance;


            var interest_in_absolute2 = self.addCommas(interest_in_absolute);

            var property_tax2 = self.addCommas(property_tax);

            var home_insurance2 = self.addCommas(home_insurance);

            //var pmi2 = self.addCommas(pmi);

            var payment_total2 = self.addCommas(payment_total);
            
            $con_wrapper.find('.principal-interest-val').html(interest_in_absolute2);
            $con_wrapper.find('.property-tax-val').html(property_tax2);
            $con_wrapper.find('.home-insurance-val').html(home_insurance2);

            $con_wrapper.find('.principal-interest-st').css("width", (interest_in_absolute2 / payment_total)*100 + '%' );
            $con_wrapper.find('.property-tax-st').css("width", (property_tax2 / payment_total)*100 + '%' );
            $con_wrapper.find('.home-insurance-st').css("width", (home_insurance2 / payment_total)*100 + '%' );

            //$con_wrapper.find('.property-pmi-val').html(pmi2);
            $con_wrapper.find('.monthly-payment-val .price-text').html(payment_total2);
            
            var ctx = $con_wrapper.find('.mortgage-calculator-chart').get(0).getContext("2d");

            if (typeof calDoughnutChart !== 'undefined') {
                calDoughnutChart.destroy();
            }

            var principal_interest_color = $con_wrapper.find('.mortgage-calculator-chart').data('principal_interest');
            var property_tax_color = $con_wrapper.find('.mortgage-calculator-chart').data('property_tax');
            var home_insurance_color = $con_wrapper.find('.mortgage-calculator-chart').data('home_insurance');

            var calDoughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [interest_in_absolute, property_tax, home_insurance],
                        backgroundColor: [
                            principal_interest_color,
                            property_tax_color,
                            home_insurance_color,
                        ]
                    }]
                },
                options: {
                    cutoutPercentage: 75,
                    responsive: false,
                    tooltips: false,
                }
            });
        },
        mortgageCalculatorWidget: function() {
            $('#btn_mortgage_get_results').on('click', function (e) {
                e.preventDefault();

                var property_price = $('#apus_mortgage_property_price').val();
                var deposit = $('#apus_mortgage_deposit').val();
                var interest_rate = parseFloat($('#apus_mortgage_interest_rate').val(), 10) / 100;
                var years = parseInt($('#apus_mortgage_term_years').val(), 10);
                

                var interest_rate_month = interest_rate / 12;
                var nbp_month = years * 12;

                var loan_amount = property_price - deposit;
                var monthly_payment = parseFloat((loan_amount * interest_rate_month) / (1 - Math.pow(1 + interest_rate_month, -nbp_month))).toFixed(2);

                if (monthly_payment === 'NaN') {
                    monthly_payment = 0;
                }
                
                $('.apus_mortgage_results').html( homez_property_opts.monthly_text + homez_property_opts.currency + monthly_payment);

            });
        },
        dashboardChartInit: function() {
            var self = this;
            var $this = $('#dashboard_property_chart_wrapper');
            if( $this.length <= 0 ) {
                return;
            }

            // select2
            if ( $.isFunction( $.fn.select2 ) && typeof wp_realestate_select2_opts !== 'undefined' ) {
                var select2_args = wp_realestate_select2_opts;
                select2_args['allowClear']              = false;
                select2_args['minimumResultsForSearch'] = 10;
                
                if ( typeof wp_realestate_select2_opts.language_result !== 'undefined' ) {
                    select2_args['language'] = {
                        noResults: function(){
                            return wp_realestate_select2_opts.language_result;
                        }
                    };
                }
                
                select2_args['width'] = '100%';

                $('.stats-graph-search-form select').select2( select2_args );
            }


            var property_id = $this.data('property_id');
            var nb_days = $this.data('nb_days');
            self.dashboardChartAjaxInit($this, property_id, nb_days);

            $('form.stats-graph-search-form select[name="property_id"]').on('change', function(){
                $('form.stats-graph-search-form').trigger('submit');
            });

            $('form.stats-graph-search-form select[name="nb_days"]').on('change', function(){
                $('form.stats-graph-search-form').trigger('submit');
            });

            $('form.stats-graph-search-form').on('submit', function(e){
                e.preventDefault();
                var property_id = $('form.stats-graph-search-form select[name="property_id"]').val();
                var nb_days = $('form.stats-graph-search-form select[name="nb_days"]').val();
                self.dashboardChartAjaxInit($this, property_id, nb_days);
                return false;
            });
        },
        dashboardChartAjaxInit: function($this, property_id, nb_days) {
            var self = this;
            if( $this.length <= 0 ) {
                return;
            }
            var parent_div = $this.parent();
            if ( parent_div.hasClass('loading') ) {
                return;
            }
            parent_div.addClass('loading');

            var ajaxurl = homez_property_opts.ajaxurl;
            if ( typeof wp_realestate_opts.ajaxurl_endpoint !== 'undefined' ) {
                ajaxurl =  wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'wp_realestate_get_property_chart' );
            }

            $.ajax({
                url: ajaxurl,
                type:'POST',
                dataType: 'json',
                data: {
                    action: 'wp_realestate_get_property_chart',
                    property_id: property_id,
                    nb_days: nb_days,
                    nonce: $this.data('nonce'),
                }
            }).done(function(response) {
                if (response.status == 'error') {
                    $this.remove();
                } else {
                    var ctx = $this.get(0).getContext("2d");

                    var data = {
                        labels: response.stats_labels,
                        datasets: [
                            {
                                label: response.stats_view,
                                backgroundColor: response.bg_color,
                                borderColor: response.border_color,
                                borderWidth: 1,
                                data: response.stats_values
                            },
                        ]
                    };

                    var options = {
                        //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
                        scaleBeginAtZero : true,
                        //Boolean - Whether grid lines are shown across the chart
                        scaleShowGridLines : false,
                        //String - Colour of the grid lines
                        scaleGridLineColor : "rgba(0,0,0,.05)",
                        //Number - Width of the grid lines
                        scaleGridLineWidth : 1,
                        //Boolean - Whether to show horizontal lines (except X axis)
                        scaleShowHorizontalLines: true,
                        //Boolean - Whether to show vertical lines (except Y axis)
                        scaleShowVerticalLines: true,
                        //Boolean - If there is a stroke on each bar
                        barShowStroke : false,
                        //Number - Pixel width of the bar stroke
                        barStrokeWidth : 2,
                        //Number - Spacing between each of the X value sets
                        barValueSpacing : 5,
                        //Number - Spacing between data sets within X values
                        barDatasetSpacing : 1,
                        legend: { display: false },

                        tooltips: {
                            enabled: true,
                            mode: 'x-axis',
                            cornerRadius: 4
                        },
                    }

                    if (typeof self.myBarChart !== 'undefined') {
                        self.myBarChart.destroy();
                    }

                    self.myBarChart = new Chart(ctx, {
                        type: response.chart_type,
                        data: data,
                        options: options
                    });
                }
                parent_div.removeClass('loading');
            });
        },
        listingBtnFilter: function(){
            $('.btn-view-map').on('click', function(e){
                e.preventDefault();
                $('#properties-google-maps').removeClass('d-none').removeClass('d-lg-block');
                $('.content-listing .properties-listing-wrapper, .content-listing .agencies-listing-wrapper').addClass('d-none').addClass('d-lg-block');
                $('.properties-filter-top-half-map ').addClass('d-none');
                $('.btn-view-listing').removeClass('d-none');
                $(this).addClass('d-none');
                $('.properties-pagination-wrapper, .agencies-pagination-wrapper').addClass('p-fix-pagination');
                setTimeout(function() {
                    $(window).trigger('pxg:refreshmap');
                }, 100);
            });
            $('.btn-view-listing').on('click', function(e){
                e.preventDefault();
                $('#properties-google-maps').addClass('d-none').addClass('d-lg-block');
                $('.content-listing .properties-listing-wrapper, .content-listing .agencies-listing-wrapper').removeClass('d-none').removeClass('d-lg-block');
                $('.btn-view-map, .properties-filter-top-half-map').removeClass('d-none');
                $(this).addClass('d-none');
                $('.properties-pagination-wrapper, .agencies-pagination-wrapper').removeClass('p-fix-pagination');
            });

            $('.show-filter-properties').on('click', function(e){
                e.stopPropagation();
                $('.layout-type-half-map .filter-sidebar').toggleClass('active');
                $('.filter-sidebar + .over-dark').toggleClass('active');
            });
            
            $(document).on('click', '.filter-sidebar + .over-dark', function(){
                $('.layout-type-half-map .filter-sidebar').removeClass('active');
                $('.filter-sidebar + .over-dark').removeClass('active');
            });

            // filter sidebar fixed
            $(document).on('click', '.properties-filter-sidebar-wrapper .close-filter, .btn-show-filter, .filter-in-sidebar, .properties-filter-sidebar-wrapper + .over-dark-filter', function(){
                $('.properties-filter-sidebar-wrapper').toggleClass('active');
            });
        },
        userLoginRegister: function(){
            var self = this;
            // login/register
            $('.user-login-form, .must-log-in').on('click', function(e){
                e.preventDefault();
                if ( $('.apus-user-login').length ) {
                    $('.apus-user-login').trigger('click');
                }
            });
            $('.apus-user-login, .apus-user-register').magnificPopup({
                mainClass: 'apus-mfp-zoom-in login-popup',
                type:'inline',
                midClick: true,
                closeBtnInside:false,
                callbacks: {
                    open: function() {
                        self.layzyLoadImage();
                    }
                }
            });
              
        },

        filterListingFnc: function(){
            var self = this;
            $('body').on('click', '.btn-show-filter, .offcanvas-filter-sidebar + .over-dark', function(){
                $('.offcanvas-filter-sidebar, .offcanvas-filter-sidebar + .over-dark').toggleClass('active');
                // $('.offcanvas-filter-sidebar').perfectScrollbar();
                if ( $('.offcanvas-filter-sidebar').length ) {
                    var ps = new PerfectScrollbar('.offcanvas-filter-sidebar', {
                        wheelPropagation: true
                    });
                }
                
            });

            $(document).on('after_add_property_favorite', function(e, $this, data) {
                $this.attr('data-original-title', homez_property_opts.favorite_added_tooltip_title);
            });
            $(document).on('after_remove_property_favorite', function( event, $this, data ) {
                $this.attr('data-original-title', homez_property_opts.favorite_add_tooltip_title);
            });

            $('body').on('click', function() {
                if ( $(this).find('.price-input-wrapper').length ) {
                    $(this).find('.price-input-wrapper').slideUp();
                }
            });

            $('body').on('click', '.form-group-price.text, .form-group-price.list, .form-group-home_area.text, .form-group-lot_area.text, .form-group-year_built.text', function(e){
                e.stopPropagation();
            });
            $('body').on('click', '.heading-filter-price', function(){
                $(this).closest('.from-to-wrapper').find('.price-input-wrapper').slideToggle();
            });
            $('body').on('keyup', '.price-input-wrapper input', function(){
                var $from_val = $(this).closest('.price-input-wrapper').find('.filter-from').val();
                var $to_val = $(this).closest('.price-input-wrapper').find('.filter-to').val();
                var $wrapper = $(this).closest('.from-to-text-wrapper');

                if ( $wrapper.hasClass('price') ) {
                    if ( wp_realestate_opts.enable_multi_currencies === 'yes' ) {
                        $from_val = self.shortenNumber($from_val);
                        $to_val = self.shortenNumber($to_val);
                    } else {
                        $from_val = self.addCommas($from_val);
                        $to_val = self.addCommas($to_val);
                    }
                    $wrapper.find('.from-text .price-text').text( $from_val );
                    $wrapper.find('.to-text .price-text').text( $to_val );
                } else {
                    $wrapper.find('.from-text').text( $from_val );
                    $wrapper.find('.to-text').text( $to_val );
                }
            });
            $('body').on('change', '.price-input-wrapper input', function(){
                var $from_val = $(this).closest('.price-input-wrapper').find('.filter-from').val();
                var $to_val = $(this).closest('.price-input-wrapper').find('.filter-to').val();
                var $wrapper = $(this).closest('.from-to-text-wrapper');
                if ( $wrapper.hasClass('price') ) {
                    if ( wp_realestate_opts.enable_multi_currencies === 'yes' ) {
                        $from_val = self.shortenNumber($from_val);
                        $to_val = self.shortenNumber($to_val);
                    } else {
                        $from_val = self.addCommas($from_val);
                        $to_val = self.addCommas($to_val);
                    }

                    $wrapper.find('.from-text .price-text').text( $from_val );
                    $wrapper.find('.to-text .price-text').text( $to_val );
                } else {
                    $wrapper.find('.from-text').text( $from_val );
                    $wrapper.find('.to-text').text( $to_val );
                }
            });
            $('body').on('click', '.from-to-wrapper.price-list .price-filter li', function(){
                var $parent = $(this).closest('.from-to-wrapper');
                var $min = $(this).data('min');
                var $max = $(this).data('max');
                $parent.find('input.filter-from').val($min);
                $parent.find('input.filter-to').val($max);
                $(this).closest('.from-to-wrapper').find('.heading-filter-price .price-text').html($(this).find('.price-text').html());
                $(this).closest('.price-input-wrapper').slideUp();
                $(this).closest('form').trigger('submit');
            });
        },
        propertyCompare: function(){
            var self = this;
            if ( $('.compare-sidebar-inner .compare-list').length ) {
                var ps = new PerfectScrollbar('.compare-sidebar-inner .compare-list', {
                    wheelPropagation: true
                });
            }

            $(document).on('after_add_property_compare', function(e, $this, data) {
                var html_output = '';
                if ( data.html_output ) {
                    html_output = data.html_output;
                }
                $('#compare-sidebar .compare-sidebar-inner').html(html_output);
                $('.compare-sidebar-btn .count').html(data.count);

                if ( $('.compare-sidebar-inner .compare-list').length ) {
                    var ps = new PerfectScrollbar('.compare-sidebar-inner .compare-list', {
                        wheelPropagation: true
                    });
                }

                self.layzyLoadImage();

                if ( !$('#compare-sidebar').hasClass('active') ) {
                    $('#compare-sidebar').addClass('active');
                }
                if ( !$('#compare-sidebar').hasClass('open') ) {
                    $('#compare-sidebar').addClass('open');
                }
                $this.attr('data-original-title', homez_property_opts.compare_added_tooltip_title);
                $this.find('span').text(homez_property_opts.compare_added_title);
            });
            

            $(document).on('after_remove_property_compare', function( event, $this, data ) {
                var html_output = '';
                if ( data.html_output ) {
                    html_output = data.html_output;
                }
                $('#compare-sidebar .compare-sidebar-inner').html(html_output);
                $('.compare-sidebar-btn .count').html(data.count);
                
                if ( $('.compare-sidebar-inner .compare-list').length ) {
                    var ps = new PerfectScrollbar('.compare-sidebar-inner .compare-list', {
                        wheelPropagation: true
                    });
                }

                if ( data.count == '0' ) {
                    $('#compare-sidebar').removeClass('active');
                }

                $this.attr('data-original-title', homez_property_opts.compare_add_tooltip_title);
                $this.find('span').text(homez_property_opts.compare_title);

                self.layzyLoadImage();
            });

            $('.compare-sidebar-inner').on('click', '.btn-remove-property-compare-list', function(e) {
                e.stopPropagation();
                var $this = $(this);
                $this.addClass('loading');
                var property_id = $(this).data('property_id');
                var nonce = $(this).data('nonce');

                var ajaxurl = homez_property_opts.ajaxurl;
                if ( typeof wp_realestate_opts.ajaxurl_endpoint !== 'undefined' ) {
                    var ajaxurl =  wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'wp_realestate_ajax_remove_property_compare' );
                }
                $.ajax({
                    url: ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data: {
                        'property_id': property_id,
                        'nonce': nonce,
                        'action': 'wp_realestate_ajax_remove_property_compare',
                    }
                }).done(function(data) {
                    $this.removeClass('loading');
                    if ( data.status ) {
                        
                        $(document).trigger( "after_remove_property_compare", [$this, data] );

                        if ( $('.btn-remove-property-compare-list').length <= 0 ) {
                            $('#compare-sidebar').removeClass('active');
                            $('#compare-sidebar').removeClass('open');
                        }

                        $('a.btn-added-property-compare').each(function(){
                            if ( property_id == $(this).data('property_id') ) {
                                $(this).removeClass('btn-added-property-compare').addClass('btn-add-property-compare');
                                $(this).attr('data-original-title', homez_property_opts.compare_add_tooltip_title);
                                $(this).find('span').text(homez_property_opts.compare_title);
                                $(this).data('nonce', data.nonce);
                            }
                        });
                    }
                });
            });

            $('.compare-sidebar-inner').on('click', '.btn-remove-compare-all', function(e) {
                e.stopPropagation();
                var $this = $(this);
                $this.addClass('loading');
                var nonce = $(this).data('nonce');

                var ajaxurl = homez_property_opts.ajaxurl;
                if ( typeof wp_realestate_opts.ajaxurl_endpoint !== 'undefined' ) {
                    var ajaxurl =  wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'wp_realestate_ajax_remove_all_property_compare' );
                }

                $.ajax({
                    url: ajaxurl,
                    type:'POST',
                    dataType: 'json',
                    data: {
                        'nonce': nonce,
                        'action': 'wp_realestate_ajax_remove_all_property_compare',
                    }
                }).done(function(data) {
                    $this.removeClass('loading');
                    if ( data.status ) {
                        
                        $(document).trigger( "after_remove_property_compare", [$this, data] );

                        $('a.btn-added-property-compare').each(function(){
                            $(this).removeClass('btn-added-property-compare').addClass('btn-add-property-compare');
                            $(this).data('nonce', data.nonce);
                        });
                    }
                });
            });

            $('body').on('click', '#compare-sidebar .compare-sidebar-btn', function(e){
                e.stopPropagation();
                $('#compare-sidebar').toggleClass('open');
            });
            $('body').on('click', function() {
                if ($('#compare-sidebar').hasClass('open')) {
                    $('#compare-sidebar').removeClass('open');
                }
            });
            $('.compare-sidebar-inner').on('click', function(e) {
                e.stopPropagation();
            });
        },
        propertySavedSearch: function() {
            var self = this;
            
            $(document).on('submit', 'form.saved-search-form2', function() {
                var $this = $(this);
                if ( $this.hasClass('loading') ) {
                    return false;
                }

                $this.find('.alert').remove();
                $this.addClass('loading');
                var parent_form_vars = '';
                var parent_form_id = $(this).data('parent-form-id');
                if ( $(parent_form_id).length ) {
                    var parent_form_vars = $(parent_form_id).serialize();
                }
                $.ajax({
                    url: wp_realestate_opts.ajaxurl_endpoint.toString().replace( '%%endpoint%%', 'wp_realestate_ajax_add_saved_search' ),
                    type:'POST',
                    dataType: 'json',
                    data: $this.serialize() + '&action=wp_realestate_ajax_add_saved_search&' + parent_form_vars
                }).done(function(data) {
                    $this.removeClass('loading');
                    if ( data.status ) {
                        $this.prepend( '<div class="alert alert-info">'+data.msg+'</div>' );
                        setTimeout(function(){
                            $.magnificPopup.close();
                        }, 1500);
                    } else {
                        $this.prepend( '<div class="alert alert-warning">'+data.msg+'</div>' );
                    }
                });

                return false;
            });

            $('.reset-search-btn').on('click', function(e){
                e.preventDefault();
                
                var $form = $( this ).closest( 'form' );

                $form.find(':input').not( ':input[type="hidden"]' ).val( '' ).trigger( 'change.select2' );
                $('.main-range-slider').each(function(){
                    var $this = $(this);
                    $this.slider("values", 0, $this.data('min'));
                    $this.slider("values", 1, $this.data('max'));
                    $this.closest('.form-group-inner').find('.filter-from').val($this.data('min'));
                    $this.closest('.form-group-inner').find('.filter-to').val($this.data('max'));

                    $this.closest('.form-group-inner').find('.from-text').val($this.data('min'));
                    $this.closest('.form-group-inner').find('.to-text').val($this.data('max'));
                });

                $form.find('.search-form-inner input:checked').each(function(i, obj) {
                    
                    $(obj).attr('checked', false);
                    $(obj).prop('checked', false);
                });

                $('.price-range-slider').each(function(){
                    var $this = $(this);
                    var $from_price = $this.data('min');
                    var $to_price = $this.data('max');

                    $this.slider("values", 0, $this.data('min'));
                    $this.slider("values", 1, $this.data('max'));

                    if ( wp_realestate_opts.enable_multi_currencies === 'yes' ) {
                        $from_price = self.shortenNumber($from_price);
                        $to_price = self.shortenNumber($to_price);
                    } else {
                        $from_price = self.addCommas($from_price);
                        $to_price = self.addCommas($to_price);
                    }

                    $this.closest('.form-group-inner').find('.from-text .price-text').text( $from_price );
                    $this.closest('.form-group-inner').find('.filter-from').val( $this.data('min') )
                    $this.closest('.form-group-inner').find('.to-text .price-text').text( $to_price );
                    $this.closest('.form-group-inner').find('.filter-to').val( $this.data('max') );

                });

                var $advanced = $(this).closest('.advance-search-wrapper-fields');

                $advanced.find(':input').not( ':input[type="hidden"]' ).val( '' ).trigger( 'change.select2' );
                $('.main-range-slider').each(function(){
                    var $this = $(this);
                    $this.slider("values", 0, $this.data('min'));
                    $this.slider("values", 1, $this.data('max'));
                    $this.closest('.form-group-inner').find('.filter-from').val($this.data('min'));
                    $this.closest('.form-group-inner').find('.filter-to').val($this.data('max'));

                    $this.closest('.form-group-inner').find('.from-text').val($this.data('min'));
                    $this.closest('.form-group-inner').find('.to-text').val($this.data('max'));
                });

                $advanced.find('input:checked').each(function(i, obj) {
                    
                    $(obj).attr('checked', false);
                    $(obj).prop('checked', false);
                });

                if ( $('.properties-listing-wrapper.main-items-wrapper').length ) {
                    $form.trigger('submit');
                }
            });
        },
        propertiesGetPage: function(pageUrl, isBackButton){
            var self = this;
            if (self.filterAjax) { return false; }

            self.propertiesSetCurrentUrl();

            if (pageUrl) {
                // Show 'loader' overlay
                self.propertiesShowLoader();
                
                // Make sure the URL has a trailing-slash before query args (301 redirect fix)
                pageUrl = pageUrl.replace(/\/?(\?|#|$)/, '/$1');
                
                if (!isBackButton) {
                    self.setPushState(pageUrl);
                }

                self.filterAjax = $.ajax({
                    url: pageUrl,
                    data: {
                        load_type: 'full'
                    },
                    dataType: 'html',
                    cache: false,
                    headers: {'cache-control': 'no-cache'},
                    
                    method: 'POST', // Note: Using "POST" method for the Ajax request to avoid "load_type" query-string in pagination links
                    
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log('Apus: AJAX error - propertiesGetPage() - ' + errorThrown);
                        
                        // Hide 'loader' overlay (after scroll animation)
                        self.propertiesHideLoader();
                        
                        self.filterAjax = false;
                    },
                    success: function(response) {
                        // Update properties content
                        self.propertiesUpdateContent(response);
                        
                        self.filterAjax = false;
                    }
                });
                
            }
        },
        propertiesHideLoader: function(){
            $('body').find('.main-items-wrapper').removeClass('loading');
        },
        propertiesShowLoader: function(){
            $('body').find('.main-items-wrapper').addClass('loading');
        },
        setPushState: function(pageUrl) {
            window.history.pushState({apusShop: true}, '', pageUrl);
        },
        propertiesSetCurrentUrl: function() {
            var self = this;
            
            // Set current page URL
            self.searchAndTagsResetURL = window.location.href;
        },
        /**
         *  Properties: Update properties content with AJAX HTML
         */
        propertiesUpdateContent: function(ajaxHTML) {
            var self = this,
                $ajaxHTML = $('<div>' + ajaxHTML + '</div>');

            var $properties = $ajaxHTML.find('.main-items-wrapper'),
                $display_mode = $ajaxHTML.find('.properties-display-mode-wrapper-ajax .properties-display-mode-wrapper'),
                $pagination = $ajaxHTML.find('.main-pagination-wrapper');

            // Replace properties
            
            if ( $properties.find('.filter-listing-form').length ) {
                var $ordering = $ajaxHTML.find('.ordering-display-mode-wrapper');
                var $items = $ajaxHTML.find('.items-wrapper');
                if ($ordering.length) {
                    $('.main-items-wrapper .ordering-display-mode-wrapper').replaceWith($ordering);
                }
                if ($items.length) {
                    $('.main-items-wrapper .items-wrapper').replaceWith($items);
                }
            } else {
                if ($properties.length) {
                    $('.main-items-wrapper').replaceWith($properties);
                }
            }

            if ($display_mode.length) {
                $('.properties-display-mode-wrapper').replaceWith($display_mode);
            }
            // Replace pagination
            if ($pagination.length) {
                $('.main-pagination-wrapper').replaceWith($pagination);
            }
            
            // Load images (init Unveil)
            self.layzyLoadImage();

            // pagination
            if ( $('.ajax-pagination').length ) {
                self.infloadScroll = false;
                self.ajaxPaginationLoad();
            }

            if ( $.isFunction( $.fn.select2 ) && typeof wp_realestate_select2_opts !== 'undefined' ) {
                var select2_args = wp_realestate_select2_opts;
                select2_args['allowClear']              = false;
                select2_args['minimumResultsForSearch'] = 10;
                select2_args['width'] = 'auto';
                
                if ( typeof wp_realestate_select2_opts.language_result !== 'undefined' ) {
                    select2_args['language'] = {
                        noResults: function(){
                            return wp_realestate_select2_opts.language_result;
                        }
                    };
                }
                
                $('select.orderby').select2( select2_args );
            }
            
            self.updateMakerCards('properties-google-maps');
            setTimeout(function() {
                // Hide 'loader'
                self.propertiesHideLoader();
            }, 100);
        },

        /**
         *  Shop: Initialize infinite load
         */
        ajaxPaginationLoad: function() {
            var self = this,
                $infloadControls = $('body').find('.ajax-pagination'),                   
                nextPageUrl;

            self.infloadScroll = ($infloadControls.hasClass('infinite-action')) ? true : false;
            
            if (self.infloadScroll) {
                self.infscrollLock = false;
                
                var pxFromWindowBottomToBottom,
                    pxFromMenuToBottom = Math.round($(document).height() - $infloadControls.offset().top);
                
                /* Bind: Window resize event to re-calculate the 'pxFromMenuToBottom' value (so the items load at the correct scroll-position) */
                var to = null;
                $(window).resize(function() {
                    if (to) { clearTimeout(to); }
                    to = setTimeout(function() {
                        var $infloadControls = $('.ajax-pagination'); // Note: Don't cache, element is dynamic
                        pxFromMenuToBottom = Math.round($(document).height() - $infloadControls.offset().top);
                    }, 100);
                });
                
                $(window).scroll(function(){
                    if (self.infscrollLock) {
                        return;
                    }
                    
                    pxFromWindowBottomToBottom = 0 + $(document).height() - ($(window).scrollTop()) - $(window).height();
                    
                    // If distance remaining in the scroll (including buffer) is less than the pagination element to bottom:
                    if (pxFromWindowBottomToBottom < pxFromMenuToBottom) {
                        self.ajaxPaginationGet();
                    }
                });
            } else {
                var $productsWrap = $('body');
                /* Bind: "Load" button */
                $productsWrap.on('click', '.main-pagination-wrapper .apus-loadmore-btn', function(e) {
                    e.preventDefault();
                    self.ajaxPaginationGet();
                });
                
            }
            
            if (self.infloadScroll) {
                $(window).trigger('scroll'); // Trigger scroll in case the pagination element (+buffer) is above the window bottom
            }
        },
        /**
         *  Shop: AJAX load next page
         */
        ajaxPaginationGet: function() {
            var self = this;
            
            if (self.filterAjax) return false;
            
            // Get elements (these can be replaced with AJAX, don't pre-cache)
            var $nextPageLink = $('.apus-pagination-next-link').find('a'),
                $infloadControls = $('.ajax-pagination'),
                nextPageUrl = $nextPageLink.attr('href');
            
            if (nextPageUrl) {
                // Show 'loader'
                $infloadControls.addClass('apus-loader');
                
                // self.setPushState(nextPageUrl);

                self.filterAjax = $.ajax({
                    url: nextPageUrl,
                    data: {
                        load_type: 'items'
                    },
                    dataType: 'html',
                    cache: false,
                    headers: {'cache-control': 'no-cache'},
                    method: 'GET',
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log('APUS: AJAX error - ajaxPaginationGet() - ' + errorThrown);
                    },
                    complete: function() {
                        // Hide 'loader'
                        $infloadControls.removeClass('apus-loader');
                    },
                    success: function(response) {
                        var $response = $('<div>' + response + '</div>'), // Wrap the returned HTML string in a dummy 'div' element we can get the elements
                            $gridItemElement = $('.items-wrapper', $response).html(),
                            $resultCount = $('.results-count .last', $response).html(),
                            $display_mode = $('.main-items-wrapper').data('display_mode');
                        

                        // Append the new elements
                        if ( $display_mode == 'grid') {
                            $('.main-items-wrapper .items-wrapper .row').append($gridItemElement);
                        } else {
                            $('.main-items-wrapper .items-wrapper').append($gridItemElement);
                        }
                        
                        // Append results
                        $('.main-items-wrapper .results-count .last').html($resultCount);

                        // Update Maps
                        self.updateMakerCards('properties-google-maps');
                        
                        // Load images (init Unveil)
                        self.layzyLoadImage();
                        
                        // Get the 'next page' URL
                        nextPageUrl = $response.find('.apus-pagination-next-link').children('a').attr('href');
                        
                        if (nextPageUrl) {
                            $nextPageLink.attr('href', nextPageUrl);
                        } else {
                            $('.main-items-wrapper').addClass('all-properties-loaded');
                            
                            if (self.infloadScroll) {
                                self.infscrollLock = true;
                            }
                            $infloadControls.find('.apus-loadmore-btn').addClass('hidden');
                            $nextPageLink.removeAttr('href');
                        }
                        
                        self.filterAjax = false;
                        
                        if (self.infloadScroll) {
                            $(window).trigger('scroll'); // Trigger 'scroll' in case the pagination element (+buffer) is still above the window bottom
                        }
                    }
                });
            } else {
                if (self.infloadScroll) {
                    self.infscrollLock = true; // "Lock" scroll (no more products/pages)
                }
            }
        },
        shortenNumber: function($number) {
            var self = this;

            var divisors = wp_realestate_opts.divisors;
            var $key_sign = '';
            $.each(divisors, function( $index, $value ) {
                if ($number < ($value['divisor'] * 1000)) {
                    $key_sign = $value['key'];
                    $number = $number / $value['divisor'];
                    return false;
                }
            });

            return self.addCommas($number) + $key_sign;
        },
        addCommas: function(str) {
            var parts = (str + "").split("."),
                main = parts[0],
                len = main.length,
                output = "",
                first = main.charAt(0),
                i;
            
            if (first === '-') {
                main = main.slice(1);
                len = main.length;    
            } else {
                first = "";
            }
            i = len - 1;
            while(i >= 0) {
                output = main.charAt(i) + output;
                if ((len - i) % 3 === 0 && i > 0) {
                    output = wp_realestate_opts.money_thousands_separator + output;
                }
                --i;
            }
            // put sign back
            output = first + output;
            // put decimal part back
            if (parts.length > 1) {
                output += wp_realestate_opts.money_dec_point + parts[1];
            }
            
            return output;
        },
        galleryPropery: function() {
            var self = this;
            $(document).on( 'mouseenter', 'article.property-item', function(){
                if ( !$(this).hasClass('loaded-gallery') && $(this).data('images') ) {
                    var $this = $(this);
                    var href = $(this).find('a.property-image').attr('href')
                    var images = $(this).data('images');
                    var html = '<div class="slick-carousel-gallery-properties hidden" style="width: ' + $(this).find('.property-thumbnail-wrapper').width() + 'px;"><div class="slick-carousel" data-items="1" data-smallmedium="1" data-extrasmall="1" data-pagination="false" data-nav="true" data-disable_draggable="true">';
                    images.forEach(function(img_url, index){
                        html += '<div class="item"><a class="property-image" href="'+ href +'"><img src="'+img_url+'"></a></div>';
                    });
                    html += '</div></div>';
                    $(this).find('.property-thumbnail-wrapper .image-thumbnail').append(html);

                    $(this).find('.slick-carousel-gallery-properties').imagesLoaded( function(){

                        $this.find('.slick-carousel-gallery-properties').removeClass("hidden").delay(200).queue(function(){
                            $(this).addClass("active").dequeue();
                        });

                        self.initSlick($this.find('.slick-carousel'));
                        
                    }).progress( function( instance, image ) {
                        $this.addClass('images-loading');
                    }).done( function( instance ) {
                        $this.addClass('images-loaded').removeClass('images-loading');
                    });

                    $(this).addClass('loaded-gallery');
                }
            });
        }
    });

    $.apusThemeExtensions.property = $.apusThemeCore.property_init;

    
})(jQuery);
