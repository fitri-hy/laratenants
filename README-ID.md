# LaraTenants – Multi-Tenant Library for Laravel 12

**LaraTenants** adalah library **multi-tenant management** untuk Laravel 12 yang memudahkan pengembangan aplikasi SaaS atau platform multi-tenant.

## Fitur utama

* Wildcard domain tenant `{tenant}.domain.com`
* Tenant default untuk domain tetap
* Tenant-specific database
* Middleware `tenant` untuk route isolation
* Model tenant-aware (`BelongsToTenant` trait)
* Tenant & user management siap pakai
* Contoh dashboard admin & user per tenant

---

## Instalasi

1. Buat project Laravel 12 baru:

```bash
composer create-project laravel/laravel multi-tenant-test
cd multi-tenant-test
```

2. Salin folder `laratenants` ke dalam project Anda dan tambahkan repository lokal di `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../laratenants",
        "options": { "symlink": true }
    }
]
```

3. Install library:

```bash
composer require fhylabs/laratenants:dev-main
```

4. Setup database MySQL di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_tenant_test
DB_USERNAME=root
DB_PASSWORD=
```

Buat database MySQL:

```sql
CREATE DATABASE multi_tenant_test;
```

5. Publish config & migration:

```bash
php artisan vendor:publish --provider="MultiTenant\MultiTenantServiceProvider" --tag=config
php artisan migrate
```

---

## Menambahkan Domain Tenant

Library `laratenants` menggunakan **tenant domain** untuk mengidentifikasi tenant aktif.

### Localhost (Windows)

Edit file `hosts`:

```
C:\Windows\System32\drivers\etc\hosts
```

Tambahkan:

```
127.0.0.1 tenant1.local.test
127.0.0.1 tenant2.local.test
```

> Untuk wildcard `{tenant}.local.test`, gunakan nama tenant spesifik seperti `abc.local.test`.

### VPS (Linux/Ubuntu)

Edit file `/etc/hosts` (opsional untuk local testing) atau gunakan DNS wildcard:

```
127.0.0.1 tenant1.vpsdomain.com
127.0.0.1 tenant2.vpsdomain.com
```

Konfigurasi Nginx untuk wildcard:

```nginx
server {
    listen 80;
    server_name  *.vpsdomain.com;

    root /var/www/multi-tenant-test/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

Reload Nginx:

```bash
sudo nginx -s reload
```

### Shared Hosting (cPanel/DirectAdmin)

1. Tambahkan subdomain untuk setiap tenant:

   * tenant1.domain.com → public_html/project
   * tenant2.domain.com → public_html/project

2. Untuk wildcard tenant, gunakan **Wildcard Subdomain** di cPanel:

   * Subdomain: `*`
   * Domain utama: `domain.com`
   * Document root: `/public_html`

---

## Penggunaan

### Menambahkan Tenant & User

Buka tinker:

```bash
php artisan tinker
```

```php
use MultiTenant\Models\Tenant;
use MultiTenant\Models\TenantUser;

// Tenant wildcard
$tenant1 = Tenant::create([
    'name' => 'Tenant 1',
    'domain' => '{tenant}.local.test',
    'database' => 'tenant1_db'
]);

// Tenant default
$tenant2 = Tenant::create([
    'name' => 'Tenant 2',
    'domain' => 'tenant2.local.test',
    'database' => 'tenant2_db'
]);

// Tenant users
TenantUser::create([
    'tenant_id' => $tenant1->id,
    'name' => 'Admin Tenant 1',
    'email' => 'admin1@tenant.com',
    'password' => bcrypt('secret')
]);

TenantUser::create([
    'tenant_id' => $tenant2->id,
    'name' => 'Admin Tenant 2',
    'email' => 'admin2@tenant.com',
    'password' => bcrypt('secret')
]);
```

---

### Route Tenant

`tenant` middleware otomatis tersedia:

```php
use Illuminate\Support\Facades\Route;
use MultiTenant\Services\TenantManager;

Route::middleware(['tenant'])->group(function () {

    // Dashboard Admin
    Route::get('/admin', function () {
        $tenant = TenantManager::getTenant();
        return view('tenant.admin-dashboard', compact('tenant'));
    })->name('tenant.admin');

    // Dashboard User
    Route::get('/user', function () {
        $tenant = TenantManager::getTenant();
        $user = auth()->user();
        return view('tenant.user-dashboard', compact('tenant', 'user'));
    })->name('tenant.user');

});
```

---

### Contoh View Dashboard

**resources/views/tenant/admin-dashboard.blade.php**

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - {{ $tenant->name }}</title>
</head>
<body>
    <h1>Welcome Admin of {{ $tenant->name }}</h1>
    <p>Tenant ID: {{ $tenant->id }}</p>
    <p>Domain: {{ $tenant->domain }}</p>
</body>
</html>
```

**resources/views/tenant/user-dashboard.blade.php**

```blade
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - {{ $tenant->name }}</title>
</head>
<body>
    <h1>Welcome {{ $user->name }} to {{ $tenant->name }}</h1>
    <p>Email: {{ $user->email }}</p>
</body>
</html>
```

---

### Tenant-Aware Model Contoh

```php
use Illuminate\Database\Eloquent\Model;
use MultiTenant\Traits\BelongsToTenant;

class Post extends Model
{
    use BelongsToTenant;
    protected $fillable = ['title', 'content'];
}
```

Migration `create_posts_table`:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
    $table->string('title');
    $table->text('content');
    $table->timestamps();
});
```

Tinker test:

```php
Post::create([
    'title' => 'Artikel Tenant 1',
    'content' => 'Isi artikel untuk tenant 1'
]);
```

> `tenant_id` otomatis sesuai tenant aktif.

---

### Menjalankan Server

```bash
php artisan serve
```

* `http://tenant1.local.test:8000` → Tenant aktif: Tenant 1
* `http://tenant2.local.test:8000` → Tenant aktif: Tenant 2