# php-shopify-app-skeleton
A bare-bones Shopify app written in plain PHP with no framework

<p>This is a very basic stripped down Shopify app that was designed to be as plug and play as possible. It does require a database setup with two tables in it and uses MySQL to work with the database. The table structures are as follows:</p>

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

The main purpose of writing this was the lack of something quick, simple, and ready to go. Most tutorials are in Ruby using the `shopify_app` gem. I tried this and had nothing but headaches getting it to work. After having to download multiple pieces of software, plugins, frameworks, etc I had an app that didn't run but still somehow managed to take up around 96MB.

So I decided to go back to the basics. This app will handle the Oauth handshake sent from Shopify as well as the added security of handling the app page view itself. This was also something I found missing from the tutorials. They handle the handshake to verify the call is coming from Shopify and then redirects to the page/location of where your app is going to run. The issue here is that at this point a hacker can simply go directly to your app location and skip around the handshake. This is asking for trouble.

This app will also be setup to be scaled in a way the would allow you to make it an external app (not embedded) and be able to have one client handle/work on multiple stores so that it can more easily be used by an agency. It also includes a column in `client_stores` named `active` that can be used to deactivate a store on the back end.


## Setup

### Clone the repo and clean up the directory
```
git clone https://github.com/XenithTech/php-shopify-app-skeleton.git my_app
cd my_app
rm -rf .git
rm README.md
rm LICENSE
```

### Create the app in Shopify
1. In your partners account (go ahead and create one if you don't have one), under `Apps` click on `Create app` in the top right.
2. Choose public (this is always a better option, in my opinion, because it has greater security measures and if you decide to make the app for another store, you only need the one instance)
3. Name your app
4. Set the `App URL` to point to `oauth.php`. This will be where your app is hosted. (ie: `https://your-app-location.com/oauth.php`)
5. Set the `Allowed redirection URL(s)` to include `postoauth.php` and `index.php`. It should look something like this:
  ```
  https://your-app-location.com/postoauth.php
  https://your-app-location.com/index.php
  ```
6. Click `Create app` in the top right

### Add your app Key and Secret Key to `config.php`
The next screen after clicking `Create app` should display these keys for you. Inside `config.php` set `APP_KEY` to the API key, and then set `APP_SECRET` to the API secret key

### Set your app permissions in `config.php`
Modify the `APP_PERMISSION` array to contain all permissions your app will need

### Connect your database in `config.php`
Create a database containing two tables with the given structure above. Be sure to create/add a user to this database. The permissions this user needs to have are at minimum `SELECT`, `UPDATE` and `INSERT`.

Inside `config.php` do the following:

1. Set `DB_HOST` to your server name. If your database is on the same server this app is hosted it will likely need to be set to `localhost`. Other wise if it is hosted elsewhere it should be set to the IP address of the server hosting the database.
2. Set `DB_USER` to the database's user account name
3. Set `DB_PASSWORD` to the user account's password
4. Set `DB_NAME` to the name of the database

### Upload your app to your server
Of course, the final step here is to upload all of the files for the app to your server. Once that is done your app should be ready to be installed on a development store. This is found under `More actions` when viewing your app inside of Shopify Partners.

## Build out your app your way
`index.php` is the home of the actual app. If you are wanting to serve the functionality of your app through some means other than PHP (ie: Node.js, React, etc) You simply need to change the `$redirection_url` to the location of your app as well as set this location as whitelisted under `Allowed redirection URL(s)` inside your app settings in your Shopify Partners account. Keep in mind that how ever you host it there is some added security in the `index.php` file that will need to be handled appropriately.
