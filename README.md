# php-shopify-app-skeleton

A bare-bones Shopify app written in plain PHP with no framework

This is a very basic stripped down Shopify app that was designed to be as plug and play as possible. It does require a database setup with two tables in it and uses MySQL to work with the database. 

## Requirements
- php 7.1 or greater
- MySql 5.6 or greater, or MariaDB 10.1 or greater
- a server (or you can use ngrok for local developement)

## Motivation

The main purpose of writing this was the lack of something quick, simple, and ready to go. Most tutorials are in Ruby  using the `shopify_app` gem or use the new shopify-cli. I tried this and had nothing but headaches getting it to work. After having to download multiple pieces of software, plugins, frameworks, etc I had an app that didn't run but still somehow managed to take up around 96MB.

So I decided to go back to the basics. This app will handle the Oauth handshake sent from Shopify as well as the added security of handling the app page view itself. This was also something I found missing from the tutorials. They handle the handshake to verify the call is coming from Shopify and then redirects to the page/location of where your app is going to run. The issue here is that at this point a hacker can simply go directly to your app location and skip around the handshake. This is asking for trouble.

This app will also be setup to be scaled in a way the would allow you to make it an external app (not embedded) and be able to have one client handle/work on multiple stores so that it can more easily be used by an agency. It also includes a column in `client_stores` named `active` that can be used to deactivate a store on the back end.


## Setup

### Clone the repo and clean up the directory (assuming you're on a *nix machine)
```
git clone https://github.com/mattiacoll/php-shopify-app-skeleton.git my_app
cd my_app
rm -rf .git README.md LICENSE
```
(optional, local developement only)
```
php -S 0.0.0.0:8080
```
and in another tab
```
ngrok http 8080
```
This should make your local app available online (**N.B.:** once you stop ngrok or shut down your machine the app is no longer available, if you want your app to be always available consider hosting it online)

### Create the database

Setup your MySQL database and update the `config.php` with your credentials

```
define( 'DB_HOST', 'your_db_host' );
define( 'DB_USER', 'your_db_user' );
define( 'DB_PASSWORD', 'your_db_password' );
define( 'DB_NAME', 'your_db_name' );
```

Navigate to your app domain /database.php (eg. https://example.com/dabase.php) to create the tables

### Create a Shopify partner's account

https://www.shopify.com/partners

### Create the app in Shopify

1. In your partners account, under `Apps` click on `Create app` in the top right.
2. Choose your app type (if you're not planning to show up in Shopify's app store choose private app)
3. Name your app
4. ⚠ Set the `App URL` to point to `/oauth.php`. This will be where your app is hosted. (ie: `https://example.com/oauth.php`)
5. Set the `Allowed redirection URL(s)` to include `/postoauth.php` and `/index.php`. It should look something like this:
  ```
  https://example.com/postoauth.php
  https://example.com/index.php
  ```
6. Click `Create app` in the top right

### Update `config.php`

The next screen after clicking `Create app` should display these keys for you. Inside `config.php` set `APP_KEY` to the API key, and then set `APP_SECRET` to the API secret key, update also `APP_URL` to point to your app URL.

```
define( 'APP_KEY', 'your_app_key' );
define( 'APP_SECRET', 'your_secret_key' );

define( 'APP_URL', 'https://example.com' );
```

### Set your app permissions in `config.php`

Modify the `APP_PERMISSION` array to contain all permissions your app will need (refer to https://shopify.dev/api/usage/access-scopes) for a list of available options

### Install the app in your store or publish it to the app store

If you've created a private app click on `Generate link` and follow the instruction. At the end you'll be given a link which you have to vist and your app will be installed on your store.

## Moving forward

Your app starts at `index.php`, you can write all your code inside there.  

If want to serve the functionality of your app through some means other than PHP (ie: Node.js, Python, etc.) you simply need to change the `APP_REDIRECT` in `config.php` to the location of your app as well as set this location as whitelisted under `Allowed redirection URL(s)` inside your app settings in your Shopify Partners account.  

Keep in mind that how ever you host it there is some added security in the `index.php` file that will need to be handled appropriately.


## Database structure

## clients
| Column name | Type         | NULL | Key | Default | Extra          |
| ---         | ---          | ---  | --- | ---     | ---            |
| client_id   | int(11)      | NO   | PRI | NULL    | auto_increment |
| client_name | varchar(255) | NO   |     | NULL    |                |

## client_stores
| Column Name   | Type         | NULL | Key | Default           | Extra          |
| ---           | ---          | ---  | --- | ---               | ---            |
| store_id      | int(11)      | NO   | PRI | NULL              | auto_increment |
| client_id     | int(11)      | NO   |     | NULL              |                |
| store_name    | varchar(255) | NO   |     | NULL              |                |
| token         | varchar(255) | NO   |     | NULL              |                |
| hmac          | varchar(255) | YES  |     | NULL              |                |
| nonce         | varchar(255) | YES  |     | NULL              |                |
| url           | varchar(255) | NO   |     | NULL              |                |
| last_activity | datetime     | NO   |     | CURRENT_TIMESTAMP |                |
| active        | tinyint(4)   | NO   |     | 1                 |                |