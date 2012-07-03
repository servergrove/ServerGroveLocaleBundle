ServerGroveLocaleBundle
=======================

This bundle provides a set of Twig functions to display flags in different ways.

[![Build Status](https://secure.travis-ci.org/servergrove/ServerGroveLocaleBundle.png?branch=master)](http://travis-ci.org/servergrove/ServerGroveLocaleBundle)

Installation
------------

You need to follow the steps according to your Symfony version.

### Symfony 2.0 ###

#### Deps ####

First you need to add the bundle to your deps file


```
[ServerGroveLocaleBundle]
    git=https://github.com/servergrove/ServerGroveLocaleBundle.git
    target=bundles/ServerGrove/LocaleBundle
```

and then, run the vendors script to download the bundle source


``` bash
$ php ./bin/vendors install
```

#### Autoload ####
The app must know where to look for our bundle classes. Adding the following line to the autoload file will do it.

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'ServerGrove' => __DIR__.'/../vendor/bundles',
));
```

#### Enable the bundle ####
Now, we need to tell our application kernel to enable the bundle. For this we need to add a bundle instance to the kernel bundles.

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new ServerGrove\LocaleBundle\ServerGroveLocaleBundle(),
    );
}
```

### Symfony 2.1 ###

Coming soon

Displaying Flags
----------------



### Simple flag ###


```
{{ flag(locale) }}
{{ flag(locale, country) }}
```

It also supports a third param for the image attributes

### Configured flags ###


```
{{ flags() }}
```

### Flag linked to a route ###
You can use the flag as a link to a route from your app. The Twig function `path_flag` will display a linked flag to the route. The function is in charge of setting the correct locale for the link.


```
{{ path_flag(route, locale) }}
{{ path_flag(route, locale, route_params, country) }}
```

### Flags linked to a route ###

This will display the flags that have been configured with a link to the `route` using the `_locale` param.


```
{{ path_flags(route) }}
{{ path_flags(route, route_params) }}
```

### Flag linked to a url ###
Flags can be associated to different URLs. There is a function that allows us to display a link to the url using the flag as a content of that link.


```
{{ url_flag("en") }} }}
```

### Flags linked to a url ###
To display all the flags that have been configured with the associated link, you have to use the `url_flags` function.


```
{{ url_flags() }}
```
