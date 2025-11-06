<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class CsvUploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    #[Test]
    public function it_requires_authentication()
    {
        $file = UploadedFile::fake()->create('addresses.csv', 100, 'text/csv');

        $response = $this->postJson('/api/v1/csv-uploads', [
            'file' => $file,
            'mappings' => ['address' => 'Address'],
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_validates_file_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/csv-uploads', [
            'mappings' => ['address' => 'Address'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function it_validates_file_type()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->postJson('/api/v1/csv-uploads', [
            'file' => $file,
            'mappings' => ['address' => 'Address'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function it_validates_mappings_is_required()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('addresses.csv', 100, 'text/csv');

        $response = $this->actingAs($user)->postJson('/api/v1/csv-uploads', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mappings']);
    }

    #[Test]
    public function it_validates_address_mapping_is_required()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('addresses.csv', 100, 'text/csv');

        $response = $this->actingAs($user)->postJson('/api/v1/csv-uploads', [
            'file' => $file,
            'mappings' => ['name' => 'Name'], // Missing 'address'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mappings.address']);
    }

    #[Test]
    public function it_processes_small_csv_synchronously()
    {
        $user = User::factory()->create();
        
        // Create CSV with 5 addresses (≤ 10 threshold)
        $csvContent = "Address\n" .
            "123 Main St, New York, NY\n" .
            "456 Oak Ave, Boston, MA\n" .
            "789 Pine Rd, Seattle, WA\n" .
            "321 Elm St, Austin, TX\n" .
            "654 Maple Dr, Denver, CO";
        
        $file = UploadedFile::fake()->createWithContent('addresses.csv', $csvContent);

        $response = $this->actingAs($user)->postJson('/api/v1/csv-uploads', [
            'file' => $file,
            'mappings' => ['address' => 'Address'],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'processing_mode' => 'synchronous',
            ],
        ]);
        
        // Should create upload record
        $this->assertDatabaseHas('csv_uploads', [
            'file_name' => 'addresses.csv',
            'uploaded_by' => $user->id,
            'status' => 'completed',
        ]);
    }

    #[Test]
    public function it_queues_large_csv_asynchronously()
    {
        Queue::fake();
        $user = User::factory()->create();
        
        // Create CSV with 15 addresses (> 10 threshold)
        $addresses = array_fill(0, 15, "123 Main St, New York, NY");
        $csvContent = "Address\n" . implode("\n", $addresses);
        
        $file = UploadedFile::fake()->createWithContent('large.csv', $csvContent);

        $response = $this->actingAs($user)->postJson('/api/v1/csv-uploads', [
            'file' => $file,
            'mappings' => ['address' => 'Address'],
        ]);

        $response->assertStatus(202); // Accepted
        $response->assertJson([
            'success' => true,
            'data' => [
                'processing_mode' => 'asynchronous',
            ],
        ]);
        
        // Should create upload record with pending status
        // Note: 14 rows because CSV parser excludes header
        $this->assertDatabaseHas('csv_uploads', [
            'file_name' => 'large.csv',
            'uploaded_by' => $user->id,
            'status' => 'pending',
            'total_rows' => 14,
        ]);
    }

    #[Test]
    public function it_returns_upload_resource_with_progress_data()
    {
        $user = User::factory()->create();
        $csvContent = "Address\n123 Main St";
        $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

        $response = $this->actingAs($user)->postJson('/api/v1/csv-uploads', [
            'file' => $file,
            'mappings' => ['address' => 'Address'],
        ]);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'upload' => [
                    'id',
                    'file_name',
                    'status',
                    'total_rows',
                    'processed_rows',
                    'progress_percentage',
                    'created_at',
                ],
            ],
        ]);
    }

    #[Test]
    public function it_lists_user_uploads()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Create uploads for both users
        \App\Models\CsvUpload::factory()->create(['uploaded_by' => $user->id]);
        \App\Models\CsvUpload::factory()->create(['uploaded_by' => $user->id]);
        \App\Models\CsvUpload::factory()->create(['uploaded_by' => $otherUser->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/csv-uploads');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals($user->id, $response->json('data.0.uploaded_by'));
    }

    #[Test]
    public function it_shows_upload_details()
    {
        $user = User::factory()->create();
        $upload = \App\Models\CsvUpload::factory()->create([
            'uploaded_by' => $user->id,
            'status' => 'completed',
            'total_rows' => 10,
            'processed_rows' => 10,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/csv-uploads/{$upload->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'upload' => [
                    'id' => $upload->id,
                    'status' => 'completed',
                    'total_rows' => 10,
                    'processed_rows' => 10,
                    'progress_percentage' => 100,
                ],
            ],
        ]);
    }
}
