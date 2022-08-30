<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>
### Introduction

trello is a laravel api to mpowers your team to manage any type of project and task tracking . This api is help you to add project and to add employees to your project and add taska and assign the task to the employee you added it is helpfull to manage your team and tells you what's being worked on, who's working on what.

### Resources you might need

https://laravel.com/docs/9.x

## Installation

-   Run Docker application first

### Prerequisites

-   PHP 8.0 or above
-   Composer
-   Xamp/Wamp for any such application for apace,nginx,mysql
-   PHP plugins you must need

-   Rename .env.example file to .env and provide necessary credentials. Like database credentials
    -   Specially check for this `env` variables
    ```
    DB_HOST=localhost
    DB_DATABASE=trello
    DB_USERNAME=root
    DB_PASSWORD=
    ```
-   Run `composer install`
-   run `php artisan key:generate`
-   run `php artisan migrate --seed` to run the seeder data to your database
-   run `php artisan serve`

## Development

-   I have provided RESTfull api.

## REST API

All the rest routes is resides in `trello/Routes/api.php` file and you can easily navigate to corresponding controller and necessary files.

### Endpoints Details

-   **[projects](https://documenter.getpostman.com/view/20146944/VUxLwU3x#049d77a9-7340-423c-a530-56c1952e467b)**
-   **[auths](https://documenter.getpostman.com/view/20146944/VUxLwU3x#30e13b3f-1503-4ed4-8a90-02635e531a65)**
-   **[task](https://documenter.getpostman.com/view/20146944/VUxLwU3x#1b056d7a-09a6-4f9d-b6a3-7a6da8fa271c)**

### config

The `config` folder contains all the `config` for the app.

### database

The `/database` folder contains all the `factories` , `migrations` and seeders.

-   #### Http:

    Contains two folders. `Controllers` and `Requests`. All the necessary controllers and requests are in this two folder.

-   #### Database:
    Contains `Models` .for database models.

## FAQ

### Changing .env files but not getting the changes

Run Below command `php artisan optimize:clear`

### Why am I getting "The GET method is not supported for this route. Supported methods: HEAD"?

Run `php artisan optimize:clear`

### The GET method is not supported for this route. Supported methods: POST. in file /Illuminate/Routing/AbstractRouteCollection.php on line 118

make sure you write the token you get in login inthe token bearer
