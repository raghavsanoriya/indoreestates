<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Env;
use Tests\TestCase;

class SubdirectoryHostingTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withServerVariables([
            'SCRIPT_NAME' => '/option-3/index.php',
            'PHP_SELF' => '/option-3/index.php',
            'SCRIPT_FILENAME' => public_path('index.php'),
        ]);
    }

    public function test_homepage_assets_and_property_links_remain_inside_the_option_prefix(): void
    {
        $property = Property::factory()->create(['title' => 'Prefix-safe Indore property', 'purpose' => 'sale']);

        $this->get('https://indore-estates.geic.in/option-3/?hosting=1')->assertOk()
            ->assertSee('https://indore-estates.geic.in/option-3/css/homez.css', false)
            ->assertSee('https://indore-estates.geic.in/option-3/source/fonts.googleapis.com/css793e.css', false)
            ->assertSee('https://indore-estates.geic.in/option-3/properties/'.$property->id, false)
            ->assertDontSee('src="/source/', false)
            ->assertDontSee('href="/source/', false);
    }

    public function test_guest_dashboard_redirects_to_its_own_prefixed_login_page(): void
    {
        $this->get('https://indore-estates.geic.in/option-3/dashboard')
            ->assertRedirect('https://indore-estates.geic.in/option-3/login');
    }

    public function test_login_form_posts_to_its_own_option_without_losing_csrf_protection(): void
    {
        $this->get('https://indore-estates.geic.in/option-3/login')->assertOk()
            ->assertSee('action="https://indore-estates.geic.in/option-3/login"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_media_paths_can_be_shared_without_sharing_framework_storage(): void
    {
        $environment = Env::getRepository();
        $environment->set('REAL_ESTATE_PRIVATE_MEDIA_PATH', '/shared/indore/private');
        $environment->set('REAL_ESTATE_PUBLIC_MEDIA_PATH', '/shared/indore/public');

        try {
            $filesystems = require config_path('filesystems.php');

            $this->assertSame('/shared/indore/private', $filesystems['disks']['local']['root']);
            $this->assertSame('/shared/indore/public', $filesystems['disks']['public']['root']);
            $this->assertSame(storage_path('framework/sessions'), config('session.files'));
        } finally {
            $environment->clear('REAL_ESTATE_PRIVATE_MEDIA_PATH');
            $environment->clear('REAL_ESTATE_PUBLIC_MEDIA_PATH');
        }
    }
}
