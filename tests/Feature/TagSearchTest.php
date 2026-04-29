<?php

namespace Tests\Feature;

use App\Models\Tag;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_seeder_inserts_at_least_200_tags(): void
    {
        $this->seed(TagSeeder::class);

        $this->assertGreaterThanOrEqual(200, Tag::query()->count());
    }

    public function test_tags_search_returns_matching_tags(): void
    {
        $this->seed(TagSeeder::class);

        $response = $this->getJson(route('tags.search', ['q' => 'caf']));

        $response->assertOk();
        $response->assertJsonStructure([
            'tags' => [
                '*' => ['id', 'name', 'slug', 'type'],
            ],
        ]);

        $tags = $response->json('tags');
        $this->assertNotEmpty($tags);
        $this->assertLessThanOrEqual(10, count($tags));
    }

    public function test_tags_search_empty_query_returns_empty_array(): void
    {
        $this->seed(TagSeeder::class);

        $response = $this->getJson(route('tags.search', ['q' => '']));

        $response->assertOk();
        $this->assertSame([], $response->json('tags'));
    }
}
