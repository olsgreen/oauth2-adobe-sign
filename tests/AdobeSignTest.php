<?php


namespace KevinEm\OAuth2\Client\Tests;

use KevinEm\OAuth2\Client\AdobeSign;
use Mockery as m;

/**
 * Class AdobeSignTest
 * @package KevinEm\OAuth2\Client\Tests
 */
class AdobeSignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdobeSign
     */
    protected $provider;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
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

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        m::close();
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
        $this->assertContains(implode('+', $options['scope']), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('secure.na1.echosign.com', $uri['host']);
        $this->assertEquals('/public/oauth', $uri['path']);
    }

    public function testGetAccessTokenUrl()
    {
        $accessToken = [
            'access_token' => 'mock_access_token'
        ];

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($accessToken));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
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

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($accessToken));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
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
        $accessToken = m::mock('League\OAuth2\Client\Token\AccessToken');
        $res = $this->provider->getResourceOwnerDetailsUrl($accessToken);
        $this->assertNull($res);
    }

    public function testCreateResourceOwner()
    {
        $resourceOwner = [];

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($resourceOwner));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $accessToken = m::mock('League\OAuth2\Client\Token\AccessToken');

        $res = $this->provider->getResourceOwner($accessToken);
        $this->assertNull($res);
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
            'dataCenter' => 'api.jp1'
        ]);

        $this->assertEquals('https://api.jp1.echosign.com/public/oauth', $provider->getBaseAuthorizationUrl());
        $this->assertEquals('https://api.jp1.echosign.com/oauth/token', $provider->getBaseAccessTokenUrl([]));
        $this->assertEquals('https://api.jp1.echosign.com/oauth/refresh', $provider->getBaseRefreshTokenUrl());
        $this->assertEquals('https://api.jp1.echosign.com/oauth/revoke', $provider->getBaseRevokeTokenUrl());
    }
}