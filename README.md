OpenSocial PHP Client Library
=============================

The OpenSocial PHP Client Library lets a PHP application work with a person's
social data on a remote network: their profile and friends, activity stream,
per-application data, messages, albums and groups. OpenSocial gave networks
such as MySpace, orkut, hi5 and Netlog a common API at the end of the 2000s,
but a developer working from the server still had to sign each request with
the right form of OAuth, choose between the REST and JSON-RPC protocols, and
work around the places where a network departed from the specification. This
library keeps that work out of the application, so that the same few calls run
against any supported network.

It is now a historical codebase. The commits here were exported from the
project's Google Code repository and run from February 2009, when an existing
library became its codebase, to April 2010. The networks it was written for
have since shut down or withdrawn these APIs, so the pre-configured providers
preserve endpoints that no longer work. The code remains useful to anyone
maintaining or studying an old OpenSocial integration, or pointing it at an
OpenSocial server they run themselves.


How it works
------------

An `osapi` object is built from a provider and a signer. The provider holds a
network's OAuth and API endpoints. It can be one of the pre-configured classes
for Google, Google Friend Connect, hi5, MySpace, Netlog, orkut, Partuza and
Plaxo, `osapiLocalPartuzaProvider` for local Partuza and Apache Shindig
instances, an `osapiProvider` with URLs you supply, or an `osapiXrdsProvider`
that discovers the endpoints through XRDS-Simple and caches them for a day.
Providers also carry each network's workarounds in hooks that run around a
request, which is how orkut receives `@viewer` in place of `@me` on writes, and
how MySpace's nested responses and HTML error pages are reduced to the shape
the rest of the library expects.

The signer authenticates each request. `osapiOAuth2Legged` signs with a consumer
key and secret on behalf of a known user, while `osapiOAuth3Legged` and
`osapiOAuth3Legged_10a` take the user through the network's authorization page
and keep the resulting access token in an `osapiStorage` back end, which can be
the filesystem, APC or Memcache. `osapiSecurityToken`, `osapiFCAuth` and
`osapiHttpBasic` cover gadget security tokens, Friend Connect tokens and HTTP
Basic.

Services such as `$osapi->people` and `$osapi->activities` only build requests,
which you add to a batch under keys of your choosing. When the batch runs, it
goes to the provider's JSON-RPC endpoint as a single POST if there is one, and
otherwise as one REST request per item. The result is an array indexed by your
keys, in which a failed request appears as an `osapiError` without affecting the
others. A failure of the transport itself throws an `osapiException`.


Getting started
---------------

There is no Composer package. Check out the repository, put its `src` directory
on PHP's include path and load the library from there. It relies on PHP's cURL
and JSON extensions, and on SimpleXML for XRDS discovery.

    set_include_path(get_include_path() . PATH_SEPARATOR
        . '/path/to/client/src');
    require_once 'osapi/osapi.php';

This fetches a user and two of their friends from a server whose endpoints you
supply, signing with two-legged OAuth:

    $provider = new osapiProvider(null, null, null,
        'http://example.org/social/rest', 'http://example.org/social/rpc',
        'Example', true);
    $auth = new osapiOAuth2Legged($consumerKey, $consumerSecret, $userId);
    $osapi = new osapi($provider, $auth);

    $batch = $osapi->newBatch();
    $batch->add($osapi->people->get(array(
        'userId' => $userId,
        'groupId' => '@self',
        'fields' => array('displayName', 'aboutMe')
    )), 'self');
    $batch->add($osapi->people->get(array(
        'userId' => $userId,
        'groupId' => '@friends',
        'count' => 2
    )), 'friends');

    foreach ($batch->execute() as $key => $item) {
        if ($item instanceof osapiError) {
            echo "$key failed: " . $item->getErrorMessage() . "\n";
        } else {
            print_r($item);
        }
    }

`src/examples` holds a page for each service, linked from `index.php`. They work
best as reading material, because `__init__.php` wires them to the historical
networks. To run one, serve `src` from a PHP-enabled web server, add a case with
your own endpoint and credentials to the `switch` in that file, select it with
`?test=`, and use a PHP older than 8, which rejects the argument order of their
`implode()` calls.


Three-legged OAuth
------------------

`osapiOAuth3Legged` implements the original OAuth 1.0 flow and carries the
request-token secret through the browser in its callback URL. Its
`performOAuthLogin()` helper runs the whole exchange and is what the samples
use. `osapiOAuth3Legged_10a` implements the 1.0a revision, which OAuth made to
close a session-fixation attack: it registers the callback when requesting the
token and sends the network's verifier when exchanging it.

The 1.0a class has a `performOAuthLogin()` of its own, but that helper still
constructs the older class and fails when it reads that class's private
properties. To use the newer flow, construct `osapiOAuth3Legged_10a` directly
and call `obtainRequestToken()`, `redirectToAuthorization()` and
`upgradeRequestToken()` yourself, keeping the token secret in storage between
the redirect and the callback.


Compatibility
-------------

The code was written for PHP 5 and has not been updated since. It targets
OpenSocial 0.8 and 0.9, and which services respond depends on the server:
albums, media items, groups and `system.listMethods` need a 0.9 server,
`statusmood`, `notifications` and `profilecomments` are MySpace extensions, and
a Portable Contacts endpoint such as Plaxo offers people data only.
`osapiCurlProvider` turns off TLS certificate verification, so patch or replace
it before sending real credentials through it.


Development
-----------

Run the tests from the repository root. They use PHPUnit's old
`PHPUnit_Framework_*` classes, so they need a PHPUnit from that era.

    phpunit test/OfflineTests.php
    phpunit test/OnlineTests.php
    phpunit AllTests test/AllTests.php

The offline suite replaces the network with `osapiLocalHttpProvider`, which
returns queued responses. The online suite calls the historical networks with
the test accounts of the time, so expect it to fail, and `AllTests` runs both.
With Xdebug installed, `phpunit --coverage-html report test/OfflineTests.php`
writes a coverage report to `report/`. API documentation comes from
phpDocumentor 1:

    phpdoc -d src -t doc -ti "OpenSocial PHP Client" -o HTML:frames:phphtmllib


Project background
------------------

- [opensocial-php-client project overview](https://chabot.dev/projects/#opensocial) on Chris Chabot’s site.

Credits and license
-------------------

Chris Chabot wrote the library this project is built on. The first commit in
this export, from February 2009, describes the code as an "updated library
originally written by Chris Chabot", and his name remains on the core client,
the OAuth signers, the REST and JSON-RPC transports, the storage classes, the
XRDS provider and most of the network providers. His later commits here added
the OAuth 1.0a class, `system.listMethods`, the Friend Connect, hi5 and Netlog
providers, and fixes to how the cURL transport handled headers.

The project itself was a shared effort. Arne Roomann-Kurrik made most of the
commits in this history, including the test structure, body-hash signing and
per-network overrides. The source credits Dan Holevoet with the HTTP provider
layer, Anash P. Oommen with the logging, and Jesse Edwards with the albums,
media items, groups and MySpace services. `src/osapi/external` bundles Andy
Smith's OAuth PHP library under the MIT License and Joseph Smarr's XRDS-Simple
parser from the DiSo project under the Apache License 2.0; see `NOTICE`.

The library is released under the Apache License 2.0; see `LICENSE`. Its source
files carry Google Inc. copyright notices or Apache Software Foundation license
headers. The original project page is archived at
https://code.google.com/archive/p/opensocial-php-client and past discussion is
at https://groups.google.com/g/opensocial-client-libraries.
