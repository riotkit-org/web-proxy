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
    
    // cache stored in the filesystem
    'cache'                  => new \Doctrine\Common\Cache\FilesystemCache(__DIR__ . '/var/cache'),
    'cacheTtl'               => 360, // cache live time, refresh every eg. 360 seconds (the list of external proxy addresses is cached)
    
    // turn off the cache
    // 'cache'               => new \Doctrine\Common\Cache\VoidCache(),
    
    // fixtures, example: Detect Facebook captcha and return a 500 response, convert all 404 to 500 error codes
    'fixtures'               => 'FacebookCaptchaTo500,NotFoundTo500',
    
    //
    // Feature: Content processor
    // When the HTML page is downloaded, then we can replace JS and CSS urls, so the will also be proxied
    //
    'contentProcessingEnabled' => true,
    
    // 
    // Feature: External IP providers
    // Use external proxies randomly to provide a huge amount of IP addresses, best option to scrap a big amount of data
    // from pages such as Facebook, Google which are blocking very quickly by showing a captcha
    //
    'externalProxyProviders'   => 'HideMyNameProvider,FreeProxyListProvider,GatherProxyProvider,ProxyListOrgProvider',
    
    // Wait 15 seconds for the connection
    'connectionTimeout'        => 15,
    
    //
    // Feature: One-time access tokens
    //   Imagine you can display an IFRAME on your page that will allow users to browse the URLs you allow
    //   So, on server side you can prepare a token, encrypt it with AES + base64 and give to the user
    //   then a user can view the specific URL through the proxy using this token
    //
    //   Token format: {"url": "http://some-allowed-url", "expires": "2017-05-05 10:20:30", "process": true, "stripHeaders": "X-Frame-Options"}
    //   GET parameter to pass token: __wp_one_time_token
    //   @see Implementation at https://github.com/Wolnosciowiec/news-feed-provider/blob/master/src/WebProxyBundle/Service/OneTimeViewUrlGenerator.php
    //
    'encryptionKey' => 'some-key',
    'oneTimeTokenStaticFilesLifeTime' => '+2 minutes',
    
    //
    // Feature: Chromium/PhantomJS prerenderer
    //   Use an external service - Wolnościowiec Prerenderer to send requests using a real browser like Chromium or PhantomJS
    //
    'prerendererUrl'           => 'http://my-prerenderer-host',
    'prerendererEnabled'       => true
];
```

#### External providers list

To redirect incoming traffic through an external proxy server you can set an external proxy provider.
This will fetch a list of IP addresses of proxy servers that will be used to redirect the traffic.

Use `externalProxyProviders` configuration parameter, or `WW_EXTERNAL_PROXIES` environment variable.

- FreeProxyCzProvider
- FreeProxyListProvider
- GatherProxyProvider
- ProxyListOrgProvider
- HideMyNameProvider
- UsProxyOrgProvider

To make sure that the proxy list is ALWAYS UP TO DATE you can put into crontab a script:
`./bin/rebuild-proxy-list`

```
# fetch the list of proxy IP addresses from providers selected in configuration
# and verify all proxy addresses one-by-one to make sure that everything is fresh
*/8 * * * * php ./bin/rebuild-proxy-list
```

How to use
==========

Make a request, just as usual. For example POST facebook.com, but move the target url to the header "WW_TARGET_URL"
and as a URL temporarily set your proxy address.

So, the `web-proxy` will redirect all headers, parameters and body you will send to it except the `WW_` prefixed.

##### Example request

```
GET / HTTP/1.1
ww-target-url: http://facebook.com/ZSP-Związek-Wielobranżowy-Warszawa-290681631074873
ww-token: your-api-key-here
ww-no-external-proxy: false

```

##### Example request through Chromium/PhantomJS + external proxy

- External proxy is used (from various providers) eg. a proxy from Proxy-List.org
- Output is rendered by Chromium or PhantomJS using the [Wolnościowiec Prerenderer](https://github.com/Wolnosciowiec/frontend-prerenderer) (requires configuration + hosting)

```
GET /__webproxy/render HTTP/1.1
Host: webproxy.localhost
ww-token: your-api-key-here
ww-url: https://facebook.com
ww-process-output: false

```

##### Example request with Chromium/PhantomJS without external proxy

- A webproxy service IP address is used
- Output is rendered by Chromium/PhantomJS

```
GET /__webproxy/render HTTP/1.1
Host: webproxy.localhost
ww-token: your-api-key-here
ww-url: https://facebook.com
ww-process-output: false
ww-no-external-proxy: true

```

##### Example request to get only external proxy details

```
GET /__webproxy/get-ip HTTP/1.1
Host: webproxy.localhost
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

Fixtures
========

Fixtures are response fixing middlewares.
Example fixture is `FacebookCaptchaTo500` which is detecting the captcha on facebook.com, if its present then HTTP response status code will
be changed to `500`.

Example of enabling a fixture using an environment variable:
```
export WW_FIXTURES="FacebookCaptchaTo500,SomethingElse" 
```

Example using config:
```
return [
    'fixtures' => 'FacebookCaptchaTo500',
];
```

[Read more about the fixtures](./docs/Fixtures.md)

Special endpoints
=================

```
ProxySelector
-------------
  Returns the IP address with port of a proxy which normally would be used to redirect the traffic
  Token is required to use the endpoint.
  
  Useful when need to render a page using eg. Chromium, so the browser could be spawn with proper arguments.
  See: https://github.com/Wolnosciowiec/frontend-prerenderer

GET /__webproxy/get-ip
```

```
Renderer
--------
  Renders the page with Chromium/PhantomJS using an external service Wolnościowiec Prerenderer.
  See: https://github.com/Wolnosciowiec/frontend-prerenderer
  
GET /__webproxy/render HTTP/1.1
Host: webproxy.localhost
ww-token: your-api-key-here
ww-url: https://facebook.com
ww-process-output: false
```
