<?php

// src/AppBundle/DataProvider/SabreRetrieveTokenAction.php

namespace AppBundle\Action;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Airport;
use AppBundle\Entity\Flight;
use AppBundle\Entity\PostalAddress;
use AppBundle\Entity\GeoCoordinates;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use \DateTime;
use \DateInterval;
use Unirest;
// use Mashape\UnirestPhp\Unirest;

final class SabreRetrieveTokenAction
{
  protected $clientId;
  protected $clientSecret;
  protected static $currentToken;
  protected static $lastDateTime;

  public function __construct()
    {

    }

  public function getSabreToken($id, $secret) {
    // dump($id, $secret);
    $clientId = $id;
    // $encodedId = base64_encode($clientId);
    $encodedId = $clientId;
    $clientSecret = $secret;
    // $encodedSecret = base64_encode($clientSecret);
    $encodedSecret = $clientSecret;
    $id = base64_encode($encodedId.':'.$encodedSecret);
    $urlToken = 'https://api.sabre.com/v2/auth/token';
    $urlToken = 'https://api.test.sabre.com/v2/auth/token';
    // $urlToken = 'https://api.havail.sabre.com/v2/auth/token';
    $headersToken= array('Authorization' => 'Basic '.$id, 'Content-Type' => 'application/x-www-form-urlencoded', 'Accept' => '*/*' );
    $payload = 'grant_type=client_credentials';
    $responseToken = Unirest\Request::post($urlToken,$headersToken,$payload);
    dump($responseToken);
    $toky = $responseToken->body->token_type . ' ' . $responseToken->body->access_token;
    // dump($responseToken);
    dump($toky);
    // $this->currentToken = $toky;
    return $toky;
  }

    public function __invoke($id, $secret)
    {
        return $this->getSabreToken($id, $secret);
    }
}
