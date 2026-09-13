<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Category::create([
            'content' => 'テストカテゴリー',
        ]);
    }

    public function test_contacts_can_be_retrieved(): void
    {
        Contact::factory()->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_contacts_can_be_filtered_by_keyword(): void
    {
        Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'sato@example.com',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=山田');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_contacts_can_be_filtered_by_gender(): void
    {
        Contact::factory()->create([
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'gender' => 2,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_contacts_can_be_filtered_by_category(): void
    {
        $category1 = Category::create([
            'content' => '商品について',
        ]);

        $category2 = Category::create([
            'content' => 'その他',
        ]);

        Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $response = $this->getJson(
            '/api/v1/contacts?category_id='.$category1->id
        );

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_contacts_can_be_filtered_by_date(): void
    {
        $contact1 = Contact::factory()->create([
            'created_at' => '2026-09-10 10:00:00',
        ]);

        $contact2 = Contact::factory()->create([
            'created_at' => '2026-09-09 10:00:00',
        ]);

        $response = $this->getJson(
            '/api/v1/contacts?date=2026-09-10'
        );

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_contacts_can_be_paginated(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson(
            '/api/v1/contacts?page=1&per_page=2'
        );

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_invalid_parameters_return_422(): void
    {
        $response = $this->getJson(
            '/api/v1/contacts?gender=5&per_page=101'
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'gender',
                'per_page',
            ]);
    }
}
