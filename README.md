Wolnościowiec Web Proxy
=======================

[![Build Status](https://travis-ci.org/Wolnosciowiec/webproxy.svg?branch=master)](https://travis-ci.org/Wolnosciowiec/webproxy)
[![Code quality](https://scrutinizer-ci.com/g/Wolnosciowiec/webproxy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Wolnosciowiec/webproxy/)

Anonymous HTTP proxy that forwards all requests through the PHP application
on server side.

```
/*
 * Wolnościowiec / WebProxy
 * ------------------------
 *
 *   Web Proxy passing through all traffic on port 80
 *   A part of an anarchist portal - wolnosciowiec.net
 *
 *   Wolnościowiec is a project to integrate the movement
 *   of people who strive to build a society based on
 *   solidarity, freedom, equality with a respect for
 *   individual and cooperation of each other.
 *
 *   We support human rights, animal rights, feminism,
 *   anti-capitalism (taking over the production by workers),
 *   anti-racism, and internationalism. We negate
 *   the political fight and politicians at all.
 *
 *   http://wolnosciowiec.net/en
 *
 *   License: LGPLv3
 */
````

Installation
============

```
export WW_TOKEN="your-api-key-here"
composer install
php -S 0.0.0.0:8081 ./index.php
```

How to use
==========

Make a request, just as usual. For example POST facebook.com, but move the target url to the header "WW_TARGET_URL"
and as a URL temporarily set your proxy address.

Example request:

```
GET http://some-proxy-host:8081/

Headers:
WW_TARGET_URL: http://facebook.com/ZSP-Związek-Wielobranżowy-Warszawa-290681631074873
WW_TOKEN: your-api-key-here
```