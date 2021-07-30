<?php


namespace App\Service;


use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AutherizedApiAccess
{
    private $api_key_param = '';
    private $api_secret_param = '';


    /**
     * AutherizedApiAccess constructor.
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->api_key_param=$parameterBag->get('api_key');
        $this->api_secret_param=$parameterBag->get('api_secret');
    }

    public function authorize( $request):bool
    {
//        $data = array();
        $apikey = $request->headers->get('api_key');
        $apiSecret =$request->headers->get('api_secret');
        $response=true;
        if($apikey=="" || $apiSecret=="")
        {
            $response=false;
        }
        elseif( $apikey!== $this->api_key_param || $apiSecret!==$this->api_secret_param)
        {
            $response=false;
        }
  return $response;
    }
}