<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_export_csv(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/contacts/export');

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'text/csv; charset=UTF-8'
        );
    }

    public function test_csv_has_bom_and_headers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/contacts/export');

        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $header = substr($content, 3);
        $firstLine = strtok($header, "\r\n");

        $this->assertSame(
            'ID,氏名,性別,メール,電話,住所,建物,カテゴリ,内容,作成日時',
            $firstLine
        );
    }

    public function test_csv_export_can_be_filtered_by_keyword(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => '商品についてのお問い合わせ',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'detail' => 'その他のお問い合わせ',
        ]);

        $response = $this->actingAs($user)
            ->get('/contacts/export?keyword=山田');

        $content = $response->streamedContent();

        $this->assertStringContainsString('山田', $content);
        $this->assertStringContainsString('yamada@example.com', $content);

        $this->assertStringNotContainsString('佐藤', $content);
        $this->assertStringNotContainsString('sato@example.com', $content);
    }

    public function test_csv_export_validation_fails_with_invalid_gender(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/contacts/export?gender=5');

        $response->assertSessionHasErrors('gender');
    }
}
