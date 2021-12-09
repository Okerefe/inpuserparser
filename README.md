
# InpUserParser
![Screenshot of the InpUserParser Public Facing Page](public/img/inpuser_shot.png)

### About
>InpUserParser is a lightweight and easy-to-install Wordpress plugin.
It Offers comprehensive parsing of user data from a specific REST API endpoint : [`https://jsonplaceholder.typicode.com/users`](https://jsonplaceholder.typicode.com/users)
>[![GPLv2 License](https://img.shields.io/badge/license-GPL--2.0-orange)](https://github.com/10up/wp_mock/blob/trunk/LICENSE.md)



### Requirements:
To install and get the plugin running. all that is needed is a WordPress installation on a machine with git and composer already installed.
npm is needed if further development of the front end code (Vue.js) or running of front end tests needs to be done.
It is also essential that PHP 7+ is available


### Installation:
The Plugin could be installed easily by cloning its repository into any WordPress installation plugin directory
and then pulling the composer dependencies
to install the plugin, cd into your WordPress plugin directory as shown

```bash
$ cd <wordpress-core path>/wp-content/plugins
```

when you are in the plugin folder, clone the repo

```bash
$ git clone https://github.com/Okerefe/inpuserparser.git
```
after which you would cd into the plugin directory:
```bash
$ cd inpuserparser
```
and then download the composer dependencies as shown:

```bash
$ composer update
```
This is all you need to get the WordPress plugin running.
For the front-end assets, the needed js files have already been compiled so if further developments are not needed, or there is no need to run the Vue.js feature tests, there is no need to install them.
But if one needs the front end assets to be available, it can be installed as shown:

```bash
$ npm install
```
This command installs the needed node packages into the node_modules/ folder

### Activation
To activate the plugin. visit the plugins settings page on Wordpress's admin dashboard and you would find the plugin among the listings there: "InpUserParser".
Click on the activation link and after activation, a link to the settings page controlling functionalities of the plugin will appear below the plugin listing. Click on this link to visit the plugins settings page


After installation, the plugins public page can be visited by navigating to the following link: ```http://wordpress.site/?inpuserparser``` where wordpress.site is the WordPress public site URL


### Usage
> Usage of the InpUserParser on the public-facing page

when the plugin page loads. the users from the endpoint are loaded through ajax.
What is loaded by default are just a few of the users' data.
to view the rest details of any user, click on any of the table entries for that user.

when an entry is clicked, a modal popup appears and shows all the details pertaining to the user.
the reason for loading these details in a modal popup is so it will also work seamlessly on mobile user agents where space is at a premium.

to dismiss the modal, click on the close link at the button right corner or the ```X``` button at the top right corner

##### Search Feature
Searches can also be made on different users using specific parameters. 
this parameter could be their id, name, username, etc.
at the right-hand side of the search bar, there is a select option where you can select what column you wish to use in searching for a user

when you start typing, the search loads automatically when the search string is 3 characters and above.


### Configuring InpUserParser on settings Page
The InpUserParser Plugin is highly customizable.
from the settings page, one can choose which column one wishes to be available on the public-facing page. although there are some compulsory columns that can't be fashioned out

one can also choose what search parameters can be available on the front-facing page of the plugin and even disable search entirely.
when this is done, the search bar is no longer loaded on the public-facing side of the plugin


### Implementations and their rationale

##### Search Functionality (the reason behind the implementation and its efficiency)
The search functionality was added to ease a user that wishes to search for someone by a range of given parameters. this would help a user to easily navigate and find specific users easily especially if or when the number of users loading from the external API becomes many.

Different ranges of parameters that a user can search by are also made available so one can search for a user using any of the user's details that one remembers from the list of the ones provided.

though the search function seems great, it has some drawbacks in terms of efficiency in the inner workings. for the search function to work, it has to first load all the data on the server and loop through each of them to get the required one.
this could be made much faster if the search is been done straight on the database to avoid many processes. but we do not have access to this database.
although since the request is cached, performance is altogether increased


#### Caching
The Plugin also caches requests from the rest endpoint to WordPress and this cache lasts for the amount of time (max-age) specified by the response headers of the endpoint from where the request is made.
this helps in speeding up the process and avoiding errors when the end rest point server is not available.

#### Error Handling
Error handling is also built into the plugin. there is both client-side error handling and server-side error handling.
the client-side error handlers work to handle unsuccessful requests to the server hosting the WordPress and give relevant error messages when this is unsuccessful.

while the server-side handle unsuccessful API calls to the endpoint and displays necessary errors in order not to disrupt navigation when a request fails

### Front End Architecture
The Frontend has been updated from pure vanilla JavaScript to the Vue.js framework.
It was developed using the best TDD practices.
This has helped to add order, structure, and yes beauty to the front-end code as opposed to using Vanilla Js.
The front-end assets were compiled using laravel-mix. a package that provides an easy-to-use, ready babel configuration.

#### Compliance and Code standards
The source code of the plugin was built with PHP 7 standards. while the front end was done with Vue.js
The backend source code (apart from the unit tests) of the plugin is also compliant with the [`inpsyde code styles`](https://github.com/inpsyde/php-coding-standards)
WordPress version 5.0 and later version have been tested with the plugin and fully supports it. earlier versions could support it but it has not been extensively tested yet.

#### Internationalization
The Plugin has also been Internationalized therefore it supports translation to different languages.


#### Unit Tests
Extensive automated unit tests are also built into the backend and extensive feature tests to the backend.
this was done using best TDD practice and will guide further upgrades and additions of features to be done less stressfully without breaking the code.

to run the tests for the backend, run the command as shown:
```bash
$ ./vendor/bin/phpunit
```
This runs all the PHP unit tests in the plugin

while to run tests for the Vue.js front end components, run the command as shown:
```bash
$ npm test
```


#### Further Possible Improvements
##### Multi-Column Search Feature
although this could be less fast, it could serve more. a multi-column search feature would allow someone to search through all the given users by any of their properties. that means there will be no need to specify what column you wish to search by cause any search made will attempt to search all users and all their properties.

##### Extensibility of Plugin
The plugin could be made extensible and customizable via hooks to allow other plugins to use all its functionalities including the search feature, etc.

Please feel free to contribute and suggest further improvements of this plugin.
##### ```Gracias....```



.