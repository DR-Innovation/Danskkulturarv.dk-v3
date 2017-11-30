Danskkulturarv.dk-v3
====================

Oppetid seneste måned  
<img src="https://app.statuscake.com/button/index.php?Track=Bzj9GSgakq&Days=30&Design=6" />

The third version of the danskkulturarv.dk website

## Setting up a developers environment

Prerequisites is a PHP interpreting webserver, such as Apache2 with MySQL. On Ubuntu you might want to install:
  - Apache2: `sudo apt-get install apache2`
  - MySQL (and it's Apache2 + PHP module): `sudo apt-get install mysql-server libapache2-mod-auth-mysql`
  - PHP (+ it's Apache2 module and relevant PHP modules): `sudo apt-get install php5 libapache2-mod-php5 php5-curl php5-gd php5-mysql php5-imagick`

Clone the repository and initialize + update it's submodules and their submodules recursively

`git clone git@github.com:DR-Innovation/Danskkulturarv.dk-v3.git`

`cd Danskkulturarv.dk-v3`

`git submodule update --recursive --init`

Setup an Apache site that serves the project-files on ex _local.danskkulturarv.dk_ (assumed from now on) and consider modifying your machines /etc/hosts or equivalent to point that domain to your loopback IP address (127.0.0.1).

Setup a database schema and user on your local MySQL database server (using phpMyAdmin, MySQL workbench, the MySQL CLI Client or something similar).

Go to http://local.danskkulturarv.dk/ and follow the setup guide, login with the admin account you've just created.

Go change the WP_DEBUG setting to true in the wp-config.php that was just created. This will let you see stacktraces when stuff might go wrong locally.

Activate the desired Wordpress Plugins via http://local.danskkulturarv.dk/wp-admin/plugins.php:
- WordPress Chaos Client
- WordPress Chaos Search
- WordPress DKA
- WP DKA Collections
-	WP DKA Custom Dashboard
- WP DKA Program Listings
- WP DKA Tags

Export / import the following pages from production into your local environment:
- ["Forside"](http://www.danskkulturarv.dk/wp-admin/post.php?post=787&action=edit)
- ["Søgeresultater"](http://www.danskkulturarv.dk/wp-admin/post.php?post=4&action=edit) (remember to correct the slug)
- ["Indhold & rettigheder"](http://www.danskkulturarv.dk/wp-admin/post.php?post=138&action=edit)

Transfer over the CHAOS settings from the production site: http://www.danskkulturarv.dk/wp-admin/options-general.php?page=wpchaos-settings

Copy over the CHAOS settings from the production environment to the CHAOS settings page on http://www.danskkulturarv.dk/wp-admin/options-general.php?page=wpchaos-settings. Consider generating a new client UUID.

Setup the widgets: http://local.danskkulturarv.dk/wp-admin/widgets.php - specifically the "Top", "CHAOS Object - Fremhævet", "CHAOS Object - Primær" and "CHAOS Object - Sidebar"


### Frontpage query

To edit what's displayed on the frontpage simple edit and/or add a `DKA Frontpage Featured` widget

![screen shot 5](https://cloud.githubusercontent.com/assets/3859425/15181911/05ab771c-178a-11e6-9ca7-0c4194f42eb5.png)

Get the search query by going to test.danskkulturarv.dk, make a search and add `?solr-debug` to the end of the url. Then copy the query from the second chaos object:

![screen shot 4](https://user-images.githubusercontent.com/190005/33328048-415de2da-d459-11e7-8b76-523e03c903b6.png)

