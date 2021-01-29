<?php


namespace Tests;

use Mockery;
use Olsgreen\OAuth2\Client\Provider\AdobeSign;
use Olsgreen\OAuth2\Client\Provider\NotImplementedException;

/**
 * Class AdobeSignTest
 * @package Tests
 */
class AdobeSignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AdobeSign
     */
    protected $provider;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->provider = new AdobeSign([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
            'scope' => [
                'mock_scope:type'
            ]
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testScopes()
    {
        $options = [
            'scope' => [
                'user_login:account',
                'agreement_send:account'
            ]
        ];

        $url = $this->provider->getAuthorizationUrl($options);
        $this->assertStringContainsString(implode('+', $options['scope']), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('secure.na1.adobesign.com', $uri['host']);
        $this->assertEquals('/public/oauth', $uri['path']);
    }

    public function testGetAccessTokenUrl()
    {
        $accessToken = [
            'access_token' => 'mock_access_token'
        ];

        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($accessToken));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals($token->getToken(), 'mock_access_token');
    }

    public function testGetAccessTokenUrlForRefreshToken()
    {
        $accessToken = [
            'access_token' => 'mock_access_token'
        ];

        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($accessToken));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('refresh_token', ['refresh_token' => 'mock_refresh_token']);

        $this->assertEquals($token->getToken(), 'mock_access_token');
    }

    public function testGetBaseRefreshTokenUrl()
    {
        $this->assertNotNull($this->provider->getBaseRefreshTokenUrl());
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $this->expectException(NotImplementedException::class);

        $accessToken = Mockery::mock('League\OAuth2\Client\Token\AccessToken');
        $res = $this->provider->getResourceOwnerDetailsUrl($accessToken);
    }

    public function testCreateResourceOwner()
    {
        $this->expectException(NotImplementedException::class);

        $accessToken = Mockery::mock('League\OAuth2\Client\Token\AccessToken');
        $res = $this->provider->getResourceOwner($accessToken);
    }

    public function testDataCenterOption()
    {
        $provider = new AdobeSign([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
            'scope' => [
                'mock_scope:type'
            ],
            'dataCenter' => 'jp1'
        ]);

        $this->assertEquals('https://secure.jp1.adobesign.com/public/oauth', $provider->getBaseAuthorizationUrl());
        $this->assertEquals('https://secure.jp1.adobesign.com/oauth/token', $provider->getBaseAccessTokenUrl([]));
        $this->assertEquals('https://secure.jp1.adobesign.com/oauth/refresh', $provider->getBaseRefreshTokenUrl());
        $this->assertEquals('https://secure.jp1.adobesign.com/oauth/revoke', $provider->getBaseRevokeTokenUrl());
    }
}