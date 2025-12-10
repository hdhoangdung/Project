# Vehicle Insurance Management System (MVC)

Setup and run

1. Import schema and seed data (adjust MySQL credentials if needed):

```powershell
cd 'c:\xampp\htdocs\Projects_\mvc\sql'
mysql -u root -p < schema.sql
mysql -u root -p < seed.sql
```

2. Update DB credentials in `config/config.php`.

3. Set webserver document root to `c:\xampp\htdocs\Projects_\mvc\public` or run built-in PHP server for testing:

```powershell
cd 'c:\xampp\htdocs\Projects_\mvc\public'
php -S localhost:8080
```

4. Login: `index.php?c=Auth&m=login` (sample users created in `sql/seed.sql`).

Notes

- The system includes controllers and models under `app/Controllers` and `app/Models`.
- Original UI files from the Projects\_ folders are included by controllers when present to preserve exact HTML/CSS/JS.
