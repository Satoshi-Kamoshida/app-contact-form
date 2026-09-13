<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_contact(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリー',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'first_name' => '太郎',
                'last_name' => '山田',
                'email' => 'test@example.com',
            ]);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);

        $contact = Contact::where('email', 'test@example.com')->first();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_validation_fails_when_required_fields_are_empty(): void
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'category_id',
                'detail',
            ]);
    }
}
