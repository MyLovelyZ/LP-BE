<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates an administrator from the given options', function () {
    $this->artisan('admin:create', [
        '--name' => 'Humas Sekolah',
        '--email' => 'humas@pelitanusantara.sch.id',
        '--password' => 'kata-sandi-kuat',
    ])->assertSuccessful();

    $admin = User::query()->where('email', 'humas@pelitanusantara.sch.id')->sole();
    expect($admin->name)->toBe('Humas Sekolah')
        ->and(Hash::check('kata-sandi-kuat', $admin->password))->toBeTrue();
});

it('refuses an email that already has an account', function () {
    $existing = User::factory()->create();

    $this->artisan('admin:create', [
        '--name' => 'Duplikat',
        '--email' => $existing->email,
        '--password' => 'kata-sandi-kuat',
    ])->assertFailed();

    expect(User::query()->count())->toBe(1);
});

it('refuses a password shorter than eight characters', function () {
    $this->artisan('admin:create', [
        '--name' => 'Admin',
        '--email' => 'admin@pelitanusantara.sch.id',
        '--password' => 'pendek',
    ])->assertFailed();

    expect(User::query()->count())->toBe(0);
});
