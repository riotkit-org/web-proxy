Wolnościowiec Web Proxy
=======================

[![Build Status](https://travis-ci.org/Wolnosciowiec/web-proxy.svg?branch=master)](https://travis-ci.org/Wolnosciowiec/web-proxy)
[![Code quality](https://scrutinizer-ci.com/g/Wolnosciowiec/webproxy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Wolnosciowiec/webproxy/)
[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/Wolnosciowiec/web-proxy)

Anonymous HTTP proxy that forwards all requests through the PHP application on server side.

Features:
- Redirect all traffic hide behind the server where the Wolnościowiec WebProxy is set up
- Redirect all traffic through external web proxies using providers (the list of proxies is updated automatically from external provider)
- Forward all headers and cookies

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
# you can also create the "config.custom.php" with `<?php return ['apiKey' => 'your-api-key'];` to have the key stored permanently without having to pass it through shell
export WW_TOKEN="your-api-key-here" 
composer install
php -S 0.0.0.0:8081 ./web/index.php
```

To have a permanent configuration file create a file named "config.custom.php" in the main directory, it will be ignored by git.
Example syntax:

```
<?php

return [
    'externalProxyProviders' => 'FreeProxyListProvider', // use http://free-proxy-list.net as a provider
    'connectionTimeout'      => 10,
    'apiKey'                 => 'something',
];
```

How to use
==========

Make a request, just as usual. For example POST facebook.com, but move the target url to the header "WW_TARGET_URL"
and as a URL temporarily set your proxy address.

So, the `web-proxy` will redirect all headers, parameters and body you will send to it except the `WW_` prefixed.

Example request:

```
GET http://some-proxy-host:8081/

Headers:
ww-target-url: http://facebook.com/ZSP-Związek-Wielobranżowy-Warszawa-290681631074873
ww-token: your-api-key-here
```

CURL example
============

```
$headers = [/* ... */];
$headers[] = 'ww-token: my-proxy-token'
$headers[] = 'ww-target-url: http://google.com';

curl_setopt($curlHandle, CURLOPT_URL, 'https://proxy-address');
curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($curlHandle, CURLOPT_TIMEOUT, 15);
curl_setopt($curlHandle, CURLOPT_PROXY, '');
```
