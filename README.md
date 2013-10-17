offliner (tt.bench.project)
========================================

1) Download
--------------------------------

### Clone the git Repository from the main repository or fork it to your github account:

Note that you **must** have git installed and be able to execute the `git`
command.

	$ git clone https://github.com/aremnev/offliner.git

2) Installation
---------------

### a) Check your System Configuration

Before you begin, make sure that your local system is properly configured
for Symfony2. To do this, execute the following:

	$ ./app/check.php 

If you get any warnings or recommendations, fix these now before moving on. 


### b) Change the permissions of the "app/cache/" and "app/logs" directories so that the web server can write into it. 

	$ chmod 777 app/cache/ app/logs

### c) Initialize and update Submodules

	$ git submodule init
	$ git submodule update

### d) Install the Vendor Libraries

    $ php composer.phar install

### e) Change DBAL settings, create DB and update it

Create copy `app/config/parameters.yml.dist` and rename to 'parameters.yml'
SetDBAL setting in  this file. After that execute the following:

    $ ./console doctrine:database:create
    $ ./console doctrine:schema:update


#optional f) Install Assets (if they hadn't been installed in **d** step or if you want to update them )

    $ ./console assets:install web
