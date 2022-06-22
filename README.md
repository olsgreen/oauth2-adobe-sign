 # Adobe Sign Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/olsgreen/oauth2-adobe-sign.svg?style=flat-square)](https://github.com/olsgreen/oauth2-adobe-sign/releases)
[![Tests](https://github.com/olsgreen/oauth2-adobe-sign/workflows/Tests/badge.svg)](https://github.com/olsgreen/oauth2-adobe-sign/actions/runs)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package provides Adobe Sign OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

Looking for an AdobeSign API client? See [olsgreen/adobe-sign-api](https://github.com/olsgreen/adobe-sign-api).

This package requires PHP >= 7.3.

## Installation

To install, use composer:

```
composer require olsgreen/oauth2-adobe-sign
```

## Usage

Usage is the same as The League's OAuth client, using `\Olsgreen\OAuth2\Client\Provider\AdobeSign` as the provider.

### Authorization Code Flow

```php
$provider = new Olsgreen\OAuth2\Client\Provider\AdobeSign([
    'clientId'          => '{adobe-client-id}',
    'clientSecret'      => '{adobe-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
    'dataCenter'        => 'eu2'
]);

if (!isset($_GET['code'])) {

    $authorizationOptions = [
        // See documentation relating to scopes:
        // https://opensource.adobe.com/acrobat-sign/developer_guide/helloworld.html#configure-scopes
        'scope' => [
            'agreement_read',
            'agreement_write',
            'agreement_send',
            'webhook_read',
            'webhook_write',
            'webhook_retention'
        ]
    ];

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($authorizationOptions);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Data Centers

The data center should match that of the account you are trying to access, so best store the datacenter with the access token. The current datacenters are:

- na1 = North America 1
- na2 = North America 2
- eu1 = EU 1
- eu2 = EU 2
- au1 = Australia 1
- jp1 = Japan 1


## Provider Quirks

Adobe do not provide an endpoint to retrieve the current user, so `getResourceOwnerDetailsUrl()`, `createResourceOwner()` & `getResourceOwner()` will throw `NotImplmenetedException`.


## Testing

``` bash
$ ./vendor/bin/phpunit
```


## Credits

Originally forked from [kevinm/oauth2-adobe-sign](https://github.com/kevinem/oauth2-adobe-sign).

- [Oliver Green](https://github.com/olsgreen)
- [All Contributors](https://github.com/thephpleague/oauth2-github/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/oldgreen/oauth2-adobe-sign/blob/master/LICENSE) for more information.
