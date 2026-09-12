<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_input_page_is_displayed(): void
    {
        Category::create([
            'content' => '商品のお届けについて',
        ]);

        Tag::create([
            'name' => '質問',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertViewIs('contact.index');

        $response->assertViewHas('categories');

        $response->assertViewHas('tags');
    }
}
