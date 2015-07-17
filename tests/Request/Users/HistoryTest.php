<?php


use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Wubs\Trakt\ClientId;
use Wubs\Trakt\Request\Parameters\Type;
use Wubs\Trakt\Request\Parameters\Username;
use Wubs\Trakt\Request\Users\History;

class HistoryTest extends PHPUnit_Framework_TestCase
{

    public function testThatItWorks()
    {
        $client = Mockery::mock(stdClass::class . ", " . ClientInterface::class);
        $request = Mockery::mock(stdClass::class . ", " . RequestInterface::class);
        $response = Mockery::mock(stdClass::class . ", " . ResponseInterface::class);

        $client->shouldReceive("createRequest")->once()->andReturn($request);
        $response->shouldReceive("json")->once()->andReturn([]);

        $clientId = ClientId::set(getenv("CLIENT_ID"));

        $response = (new History(get_token(), 'rolle', Type::movies()))->make($clientId, $client);

        $this->assertInternalType("array", $response);
    }
}
