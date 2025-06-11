# Demo Dance

This is a demo WordPress + WooCommerce project used for teaching purposes, primarily about automated testing but also covering some plugin development and WooCommerce customisation skills and knowledge.

This site forms the case study referred to in my WordCamp Brisbane 2025 talk, _Catching all the (edge) cases: Getting started with automated testing_ and the companion resource [Time for Testing](https://timefortesting.net).

## Prerequisites

To run this project locally, you will need:
- A local PHP+MySQL web server application such as [Laravel Herd](https://herd.laravel.com/), [Local by Flywheel](https://localwp.com/), WAMP, MAMP, or XAMPP
- PHP and Composer available on the command line.

## Setup

1. Clone the repository to the appropriate directory for your local web server to find it
2. Create a database in your local MySQL server and import the `demo-dance.sql` file into it
3. Update `wp-config.php` with your local database credentials
4. Navigate to the `/wp-content/plugins/demo-custom-pricing` directory in your terminal and run `composer install`.

More information on setting up local development environments and optimising your IDE for testing (...if you're a JetBrains user) can be found in the setup section of the [Time for Testing](https://timefortesting.net/setup.html) website.

## Warranty

You are welcome to use the code from the demo custom pricing plugin in your own projects, but it is provided as-is with no warranty or guarantee of fitness for your purpose, technical support, or that future updates will not introduce uncommunicated breaking changes.
