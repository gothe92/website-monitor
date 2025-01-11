# Laravel Website Monitor

Ez az alkalmazás weboldalak válaszidejét monitorozza és Discord értesítéseket küld, ha problémát észlel.

## Funkciók

- Aszinkron weboldal monitorozás
- Válaszidő mérés és naplózás
- Automatikus Discord értesítések
- Adatbázis alapú konfiguráció
- Laravel ütemező integráció

## Telepítés

1. Klónozd le a repository-t:
```bash
git clone your-repo-url
cd your-project
```

2. Telepítsd a függőségeket:
```bash
composer install
```

3. Másold le a `.env.example` fájlt:
```bash
cp .env.example .env
```

4. Generálj alkalmazás kulcsot:
```bash
php artisan key:generate
```

5. Állítsd be az adatbázis kapcsolatot a `.env` fájlban:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Add hozzá a Discord webhook URL-t a `.env` fájlhoz:
```
DISCORD_WEBHOOK=your_discord_webhook_url
```

7. Futtasd a migrációkat:
```bash
php artisan migrate
```

## Használat

### Weboldal hozzáadása a monitorozáshoz

Használd a Laravel tinker-t vagy hozz létre egy seeder-t:

```bash
php artisan tinker
```

```php
\App\Models\Website::create([
    'url' => 'https://example.com',
    'name' => 'Example Website'
]);
```

### Monitorozás indítása

Manuális futtatás:
```bash
php artisan monitor:website-async
```

Vagy állítsd be a Laravel ütemezőt a `app/Console/Kernel.php` fájlban:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('monitor:website-async')->everyMinute();
}
```

És indítsd el az ütemezőt:
```bash
php artisan schedule:work
```

## Discord Értesítések

Az alkalmazás a következő esetekben küld értesítést:

- 🔴 Ha egy weboldal nem elérhető
- ⚠️ Ha egy weboldal válaszideje meghaladja a 10 másodpercet
- ⚠️ Ha rendszerhiba történik a monitorozás során

## Adatbázis Struktúra

### Websites Tábla
- `id` - Egyedi azonosító
- `url` - A monitorozott weboldal URL-je
- `name` - A weboldal neve
- `created_at` - Létrehozás időpontja
- `updated_at` - Utolsó módosítás időpontja

### Website_logs Tábla
- `id` - Egyedi azonosító
- `website_id` - Kapcsolódó weboldal azonosítója
- `response_time` - Válaszidő milliszekundumban
- `status` - Státusz (success/error)
- `error_message` - Hibaüzenet (ha van)
- `created_at` - Létrehozás időpontja
- `updated_at` - Utolsó módosítás időpontja

## Hibaelhárítás

1. **Discord értesítések nem érkeznek meg**
   - Ellenőrizd a DISCORD_WEBHOOK értékét
   - Ellenőrizd a webhook URL érvényességét
   - Nézd meg a Laravel log fájlokat

2. **Magas válaszidők**
   - Ellenőrizd a monitorozott weboldal szerverét
   - Nézd meg a hálózati kapcsolatot
   - Ellenőrizd a szerver erőforrásait

## Licenc

MIT License.
