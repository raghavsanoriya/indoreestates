<?php

// Editable website content. Each key's default is the site's original text, so the
// public site is unchanged until an administrator edits it from Dashboard → Website Content.
// Types: text, textarea, markdown, image, url, email, tel, faq.

$pageSeo = fn (string $page, string $label): array => [
    "seo.$page.title" => ['label' => "$label page title", 'type' => 'text', 'default' => '', 'max' => 70, 'help' => 'Leave empty to use the site default title.'],
    "seo.$page.description" => ['label' => "$label page description", 'type' => 'textarea', 'default' => '', 'max' => 160, 'help' => 'Shown in Google results. Leave empty to use the site default description.'],
];

return [
    'sections' => [
        'homepage' => [
            'label' => 'Homepage',
            'icon' => 'home',
            'groups' => [
                'Hero' => [
                    'home.hero.title' => ['label' => 'Headline', 'type' => 'text', 'default' => 'Easy Way to Find a Perfect Property', 'max' => 80, 'required' => true],
                    'home.hero.subtitle' => ['label' => 'Intro text', 'type' => 'textarea', 'default' => 'Explore homes, land and commercial properties in Indore.', 'max' => 200],
                    'home.hero.button' => ['label' => 'Button text', 'type' => 'text', 'default' => 'Explore Properties', 'max' => 40, 'required' => true],
                    'home.hero.image' => ['label' => 'Background image', 'type' => 'image', 'default' => 'source/demoapus1.com/homez/wp-content/uploads/2022/11/slider4.jpg', 'help' => 'Wide photo, at least 1600px across.'],
                ],
                'Properties in Indore' => [
                    'home.locations.title' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Properties in Indore', 'max' => 80, 'required' => true],
                    'home.locations.subtitle' => ['label' => 'Intro text', 'type' => 'textarea', 'default' => 'Explore developer projects and localities in your city.', 'max' => 200],
                    'home.locations.button' => ['label' => 'Button text', 'type' => 'text', 'default' => 'Explore Indore', 'max' => 40, 'required' => true],
                ],
                'Discover Indore Properties' => [
                    'home.listings.title' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Discover Indore Properties', 'max' => 80, 'required' => true],
                    'home.listings.subtitle' => ['label' => 'Intro text', 'type' => 'textarea', 'default' => 'Published listings and developer projects, all in one place.', 'max' => 200],
                    'home.listings.empty' => ['label' => 'Message when a tab has no properties', 'type' => 'text', 'default' => 'No published properties here yet.', 'max' => 120],
                    'home.listings.button' => ['label' => 'Button text', 'type' => 'text', 'default' => 'Explore all properties', 'max' => 40, 'required' => true],
                ],
                'Services' => [
                    'home.services.title' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Let’s find the right selling option for you', 'max' => 100, 'required' => true],
                    'home.services.item1_title' => ['label' => 'Service 1 title', 'type' => 'text', 'default' => 'Property Management', 'max' => 60, 'required' => true],
                    'home.services.item1_text' => ['label' => 'Service 1 text', 'type' => 'textarea', 'default' => 'Manage your Indore listings, save favourites and connect through your account.', 'max' => 200],
                    'home.services.item2_title' => ['label' => 'Service 2 title', 'type' => 'text', 'default' => 'Private Enquiries', 'max' => 60, 'required' => true],
                    'home.services.item2_text' => ['label' => 'Service 2 text', 'type' => 'textarea', 'default' => 'Manage your Indore listings, save favourites and connect through your account.', 'max' => 200],
                    'home.services.item3_title' => ['label' => 'Service 3 title', 'type' => 'text', 'default' => 'Save Favourites', 'max' => 60, 'required' => true],
                    'home.services.item3_text' => ['label' => 'Service 3 text', 'type' => 'textarea', 'default' => 'Manage your Indore listings, save favourites and connect through your account.', 'max' => 200],
                    'home.services.button' => ['label' => 'Button text', 'type' => 'text', 'default' => 'Learn More', 'max' => 40, 'required' => true],
                    'home.services.image' => ['label' => 'Image', 'type' => 'image', 'default' => 'source/demoapus1.com/homez/wp-content/uploads/2022/11/h41.jpg', 'help' => 'Portrait photo, about 685 × 720px.'],
                ],
                'Property types' => [
                    'home.types.title' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Explore Property Types', 'max' => 80, 'required' => true],
                    'home.types.subtitle' => ['label' => 'Intro text', 'type' => 'textarea', 'default' => 'Find the right kind of space for your next move.', 'max' => 200],
                    'home.types.button' => ['label' => 'Link text', 'type' => 'text', 'default' => 'All Types', 'max' => 40, 'required' => true],
                ],
                'Reviews' => [
                    'home.reviews.title' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Experiences with indoreestate', 'max' => 80, 'required' => true],
                ],
                'Call to action' => [
                    'home.cta.title' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Get Your Dream House', 'max' => 80, 'required' => true],
                    'home.cta.text' => ['label' => 'Text', 'type' => 'textarea', 'default' => 'Discover your next address in Indore. Save properties and get current pricing from the listing team.', 'max' => 300],
                    'home.cta.button' => ['label' => 'Button text', 'type' => 'text', 'default' => 'Get Started', 'max' => 40, 'required' => true],
                    'home.cta.image' => ['label' => 'Image', 'type' => 'image', 'default' => 'source/demoapus1.com/homez/wp-content/uploads/2022/11/h43.png', 'help' => 'Transparent PNG works best, about 444 × 511px.'],
                ],
            ],
        ],
        'pages' => [
            'label' => 'Pages',
            'icon' => 'article',
            'groups' => [
                'About Us' => [
                    'pages.about.title' => ['label' => 'Page title', 'type' => 'text', 'default' => 'About indoreestate', 'max' => 80, 'required' => true],
                    'pages.about.body' => ['label' => 'Content', 'type' => 'markdown', 'default' => "## Your Address in Indore\n\nindoreestate helps you explore properties and developer projects in Indore, Madhya Pradesh. Browse available listings, save favourites and send private enquiries through your account.\n\nImported projects identify their developer and source. Confirm current availability, specifications and pricing before making a decision.", 'max' => 20000],
                    'pages.about.image' => ['label' => 'Image', 'type' => 'image', 'default' => 'source/demoapus1.com/homez/wp-content/uploads/2022/11/h41.jpg'],
                ],
                'Our Services' => [
                    'pages.services.title' => ['label' => 'Page title', 'type' => 'text', 'default' => 'Our Services', 'max' => 80, 'required' => true],
                    'pages.services.body' => ['label' => 'Content', 'type' => 'markdown', 'default' => "## Explore, Save and Enquire\n\nSearch Indore properties by location, type, sale or rent, price and amenities. Save properties to your account and send private enquiries.\n\nAgents can submit listings through the dashboard. Listings are reviewed by an administrator before publication.", 'max' => 20000],
                ],
                'Listing Enquiries (pricing page)' => [
                    'pages.pricing.title' => ['label' => 'Page title', 'type' => 'text', 'default' => 'Listing Enquiries', 'max' => 80, 'required' => true],
                    'pages.pricing.body' => ['label' => 'Content', 'type' => 'markdown', 'default' => 'Online purchases and paid subscription plans are not available. Contact the team with your listing requirements.', 'max' => 20000],
                ],
                'Privacy' => [
                    'pages.privacy.title' => ['label' => 'Page title', 'type' => 'text', 'default' => 'Privacy Enquiries', 'max' => 80, 'required' => true],
                    'pages.privacy.body' => ['label' => 'Content', 'type' => 'markdown', 'default' => 'Please contact the indoreestate team for the current privacy policy or questions about your account and submitted information.', 'max' => 20000],
                ],
                'Property Updates (blog page)' => [
                    'pages.blog.title' => ['label' => 'Page title', 'type' => 'text', 'default' => 'Property Updates', 'max' => 80, 'required' => true],
                    'pages.blog.body' => ['label' => 'Content', 'type' => 'markdown', 'default' => "## No articles published yet\n\nBrowse our property listings for current project information.", 'max' => 20000],
                ],
                'Contact Us' => [
                    'pages.contact.title' => ['label' => 'Page title', 'type' => 'text', 'default' => 'Contact Us', 'max' => 80, 'required' => true],
                    'pages.contact.heading' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Get in Touch', 'max' => 80, 'required' => true],
                    'pages.contact.intro' => ['label' => 'Intro text', 'type' => 'textarea', 'default' => 'Contact indoreestate about properties in Indore.', 'max' => 300],
                    'pages.contact.form_title' => ['label' => 'Form heading', 'type' => 'text', 'default' => 'Send Your Enquiry', 'max' => 80, 'required' => true],
                ],
            ],
        ],
        'faq' => [
            'label' => 'FAQ',
            'icon' => 'quiz',
            'groups' => [
                'Frequently asked questions' => [
                    'pages.faq.title' => ['label' => 'Page title and heading', 'type' => 'text', 'default' => 'Frequently Asked Questions', 'max' => 80, 'required' => true],
                    'faq.items' => ['label' => 'Questions and answers', 'type' => 'faq', 'max' => 20, 'default' => [
                        ['question' => 'Where are properties located?', 'answer' => 'Public listings are for Indore only, in Madhya Pradesh.'],
                        ['question' => 'Why is a price not shown?', 'answer' => 'When a developer has not supplied a current price, use Contact us to request pricing and availability.'],
                        ['question' => 'How do I save properties?', 'answer' => 'Create a customer account or sign in, then use the favourite button on a property. Your saved properties remain in your dashboard.'],
                        ['question' => 'How do I publish a property?', 'answer' => 'An administrator must grant agent access first. Create your listing, upload photos and submit it for approval.'],
                        ['question' => 'Are payments and bookings active?', 'answer' => 'No. Online payments, paid subscriptions and automated bookings are not active. Contact the listing team to discuss next steps.'],
                    ]],
                ],
            ],
        ],
        'contact' => [
            'label' => 'Contact & Footer',
            'icon' => 'contact_phone',
            'groups' => [
                'Contact details' => [
                    'contact.city' => ['label' => 'City', 'type' => 'text', 'default' => 'Indore', 'max' => 80, 'required' => true],
                    'contact.region' => ['label' => 'State and country', 'type' => 'text', 'default' => 'Madhya Pradesh, India', 'max' => 120],
                    'contact.address' => ['label' => 'Office address', 'type' => 'textarea', 'default' => '', 'max' => 300, 'help' => 'Optional. Shown on the contact page and in the footer when filled in.'],
                    'contact.phone' => ['label' => 'Phone', 'type' => 'tel', 'default' => ''],
                    'contact.whatsapp' => ['label' => 'WhatsApp number', 'type' => 'tel', 'default' => '', 'help' => 'Include the country code, e.g. +91 98765 43210.'],
                    'contact.email' => ['label' => 'Email', 'type' => 'email', 'default' => ''],
                ],
                'Social media' => [
                    'social.facebook' => ['label' => 'Facebook page', 'type' => 'url', 'default' => ''],
                    'social.instagram' => ['label' => 'Instagram profile', 'type' => 'url', 'default' => ''],
                    'social.linkedin' => ['label' => 'LinkedIn page', 'type' => 'url', 'default' => ''],
                    'social.youtube' => ['label' => 'YouTube channel', 'type' => 'url', 'default' => ''],
                    'social.x' => ['label' => 'X (Twitter) profile', 'type' => 'url', 'default' => ''],
                ],
                'Footer' => [
                    'footer.enquiries_label' => ['label' => 'Enquiries label', 'type' => 'text', 'default' => 'Property enquiries', 'max' => 60],
                    'footer.enquiries_link' => ['label' => 'Enquiries link text', 'type' => 'text', 'default' => 'Contact our team', 'max' => 60, 'required' => true],
                    'footer.newsletter_title' => ['label' => 'Newsletter heading', 'type' => 'text', 'default' => 'Keep Yourself Up to Date', 'max' => 80, 'required' => true],
                    'footer.newsletter_text' => ['label' => 'Newsletter text', 'type' => 'textarea', 'default' => 'Sign up for property updates in Indore.', 'max' => 200],
                    'footer.copyright' => ['label' => 'Copyright text (after © year)', 'type' => 'text', 'default' => 'indoreestate. All Rights Reserved.', 'max' => 120, 'required' => true],
                    'footer.bottom_link' => ['label' => 'Bottom link text', 'type' => 'text', 'default' => 'Find your next address', 'max' => 60, 'required' => true],
                ],
            ],
        ],
        'seo' => [
            'label' => 'SEO',
            'icon' => 'travel_explore',
            'groups' => [
                'Site defaults' => [
                    'seo.default.title' => ['label' => 'Default page title', 'type' => 'text', 'default' => 'indoreestate · Properties in Indore', 'max' => 70, 'required' => true],
                    'seo.default.description' => ['label' => 'Default description', 'type' => 'textarea', 'default' => 'Explore properties and developer projects in Indore with indoreestate.', 'max' => 160, 'required' => true],
                ],
                'Homepage' => $pageSeo('home', 'Homepage'),
                'Properties list' => $pageSeo('properties', 'Properties'),
                'Contact' => $pageSeo('contact', 'Contact'),
                'About Us' => $pageSeo('about', 'About'),
                'Our Services' => $pageSeo('services', 'Services'),
                'FAQ' => $pageSeo('faq', 'FAQ'),
                'Privacy' => $pageSeo('privacy', 'Privacy'),
                'Listing Enquiries' => $pageSeo('pricing', 'Listing Enquiries'),
                'Property Updates' => $pageSeo('blog', 'Property Updates'),
            ],
        ],
    ],
];
