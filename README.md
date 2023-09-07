# Licht

**Licht** is a Laravel package that simplifies the process of generating CRUD (Create, Read, Update, Delete) operations for your models. It provides a convenient Artisan command to quickly generate the necessary components for your models, such as models, requests, resources, controllers, and migrations.

## Installation

To get started with **Licht**, follow these steps:

1. Install the package via Composer:

```bash
composer require hossamsoliuman/licht
```

# Usage

<p>To generate CRUD operations for a model, you can use the licht:crud Artisan command. Replace ModelName with the name of your model:</p>

```bash
php artisan licht:crud ModelName
```

You will be prompted to enter the field names and their types. Once you've provided the necessary information, Licht will generate the following components for you:<br>

 - Model
 - Store Request
 - Update Request
 - Resource
 - Controller
 - Migration

<p>These components will be placed in the appropriate directories within your Laravel project.</p><br>

# Features
 - Quick generation of CRUD components for your models.
 - Supports various field types, including string, integer, text, foreignId, image, file, date, and datetime.
 - Automatically generates validation rules based on field types.
 - Provides a consistent code structure for your Laravel applications.
 - Saves development time and effort.

# License

**Licht** is open-source software licensed under the MIT License.






