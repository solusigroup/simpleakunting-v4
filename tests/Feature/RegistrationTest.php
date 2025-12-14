<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CoaUmkmSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_company_and_coa(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'CV Test Company',
            'entity_type' => 'UMKM',
        ]);

        $response->assertRedirect('/dashboard');

        // Check user was created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'Administrator',
        ]);

        // Check company was created
        $this->assertDatabaseHas('companies', [
            'name' => 'CV Test Company',
            'entity_type' => 'UMKM',
        ]);

        // Check COA was seeded
        $user = User::where('email', 'test@example.com')->first();
        $coaCount = ChartOfAccount::where('company_id', $user->company_id)->count();
        $this->assertGreaterThan(0, $coaCount, 'COA should be seeded');
    }

    public function test_registration_with_bumdesa_entity(): void
    {
        $response = $this->post('/register', [
            'name' => 'BUMDesa Admin',
            'email' => 'admin@bumdesa.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'BUMDesa Makmur',
            'entity_type' => 'BUMDesa',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('companies', [
            'name' => 'BUMDesa Makmur',
            'entity_type' => 'BUMDesa',
        ]);

        // Check BUMDesa COA format (1.1.1)
        $user = User::where('email', 'admin@bumdesa.test')->first();
        $this->assertDatabaseHas('chart_of_accounts', [
            'company_id' => $user->company_id,
            'code' => '1.1.1',
        ]);
    }

    public function test_registration_requires_entity_type(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'CV Test',
        ]);

        $response->assertSessionHasErrors('entity_type');
    }
}
