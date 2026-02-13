# Laravel Migrate Backup

Automatically create a **database backup before running destructive migration commands** like `migrate:fresh` and `migrate:refresh`.

This package ensures your data is always safe by generating a timestamped SQL dump before migrations execute.

---

## ✨ Features

* 🔄 Auto backup before `migrate:fresh`
* 🔄 Auto backup before `migrate:refresh`
* 🏷️ Timestamped backup files
* 📂 Custom backup directory support
* 🧩 Works with migration flags (`--seed`, etc.)
* 🐬 MySQL / MariaDB support
* 🐘 PostgreSQL support
* 📦 SQLite file backup
* ⚡ Laravel Auto-Discovery ready
* 🛠️ Laravel 10 / 11 / 12 compatible

---

## 📦 Installation

Install via Composer:

```bash
composer require ratoguras/laravel-migrate-backup
```

---

## ⚙️ Publish Configuration

```bash
php artisan vendor:publish --tag=migrate-backup-config
```

This will publish:

```
config/migrate-backup.php
```

---

## 🧾 Configuration

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Commands to Watch
    |--------------------------------------------------------------------------
    |
    | Backups will run before these artisan commands execute.
    |
    */

    'commands' => [
        'migrate:fresh',
        'migrate:refresh',
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Path
    |--------------------------------------------------------------------------
    |
    | Relative to project base path.
    |
    */

    'path' => 'database/backups',

];
```

---

## 🚀 Usage

Run migrations as usual:

```bash
php artisan migrate:fresh
```

or

```bash
php artisan migrate:fresh --seed
```

or

```bash
php artisan migrate:refresh
```

Before execution, the package will automatically generate a backup.

---

## 📂 Backup Location

```
database/backups/
```

Example file:

```
mydatabase_2026-02-13_18-45-10.sql
```

---

## 🗄️ Supported Databases

| Database   | Supported |
| ---------- | --------- |
| MySQL      | ✅         |
| MariaDB    | ✅         |
| PostgreSQL | ✅         |
| SQLite     | ✅         |

---

## 🧠 How It Works

The package listens to Laravel’s console event:

```
Illuminate\Console\Events\CommandStarting
```

When a configured migration command is detected, it:

1. Detects default DB connection
2. Creates backup directory (if missing)
3. Generates timestamped dump
4. Stores SQL file safely

---

## 📋 Requirements

* PHP ≥ 8.2
* Laravel ≥ 10.x

---

## 🧪 Testing Backup

Run:

```bash
php artisan migrate:fresh
```

You should see:

```
✅ Database backup created: yourdb_2026-02-13_18-50-22.sql
```

---

## 🔐 Security Note

Backups may contain sensitive data.

Recommended:

* Add `/database/backups` to `.gitignore`
* Store backups securely in production

---

## 🛣️ Roadmap

Planned features:

* 🔁 Backup restore command
* 🗜️ ZIP compression
* 🧹 Auto cleanup scheduler
* ☁️ Cloud backup support
* 🪟 Windows dump compatibility

---

## 🤝 Contributing

Contributions are welcome!

1. Fork the repo
2. Create feature branch
3. Commit changes
4. Submit PR

---

## 🐞 Issues

Report bugs or request features:

👉 https://github.com/Packages-Rato-Guras/laravel-migrate-backup/issues

---

## 📄 License

MIT License © 2026 Adish Dahal

---

## 👨‍💻 Author

**Adish Dahal**
Founder & CEO — Rato Guras Technology Pvt. Ltd.

---

## ⭐ Support

If you find this package useful, please ⭐ the repository and share it with the Laravel community.
