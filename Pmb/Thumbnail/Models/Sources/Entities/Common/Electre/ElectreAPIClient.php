<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ElectreAPIClient.php,v 1.6 2024/03/19 14:16:03 dbellamy Exp $

namespace Pmb\Thumbnail\Models\Sources\Entities\Common\Electre;

use Pmb\Authentication\Models\Sources\OpenIDConnect\OpenIDConnectClient;
use GuzzleHttp\Client;
use GuzzleHttp;
use Pmb\Common\Library\ISBN\ISBN;

class ElectreAPIClient
{

    const DEFAULT_API_BASE_URL = "https://api.electre-ng.com";
    const DEFAULT_API_TOKEN_URL = "https://login.electre-ng.com/auth/realms/electre/protocol/openid-connect/token";
    const DEFAULT_CLIENT_ID = "api-client";
    const DEFAULT_MAX_RESULTS = 100;


    // const DEFAULT_API_BASE_URL = "https://api.demo.electre-ng-horsprod.com";
    // const DEFAULT_API_TOKEN_URL = "https://login.electre-ng-horsprod.com/auth/realms/electre/protocol/openid-connect/token";

    protected $api_base_url = '';
    protected $api_token_url = '';
    protected $client_id = '';
    protected $client_secret = '';
    protected $client_user;

    protected $access_token = '';

    protected $oidc_client = null;
    protected $guzzle_client = null;

    /**
     * Constructeur
     *
     * @param string $client_id : Identifiant client
     * @param string $client_secret : Secret / Password client
     * @param string $client_user : Nom utilisateur
     * @param string $api_base_url : URL de base de l'API
     * @param string $api_token_url : URL de demande de token
     * @return void
     */
    public function __construct(
        string $client_id = '',
        string $client_secret = '',
        string $client_user = '',
        string $api_base_url = ElectreAPIClient::DEFAULT_API_BASE_URL,
        string $api_token_url = ElectreAPIClient::DEFAULT_API_TOKEN_URL
        ) {

        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->client_user = $client_user;
        $this->api_base_url = $api_base_url;
        $this->api_token_url = $api_token_url;
    }


    /**
     * Getter Token d'acc�s
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Setter Token d'acc�s
     *
     * @param string $token
     */
    public function setAccessToken(string $token)
    {
        $this->access_token = $token;
    }


    /**
     * Recuperation des images a partir d'un EAN
     *
     * @param string $ean
     * @return [] : tableau d'URL d'images
     */
     public function getImagesFromEan(string $ean)
    {

        $this->renewAccessToken();
        if(empty($this->access_token)) {
            return [];
        }

        $ean = ISBN::toEAN13($ean);
        if('' == $ean) {
            return [];
        }

        $response = $this->search(['ean' => $ean], 'ean');
        $notice = $response['notices'][0] ?? [];
        $images = [];
        //Image 160px
        if(!empty($notice['image160pxCouverture'])) {
            $images[] = $notice['image160pxCouverture'];
        }
        // Image full
        if(!empty($notice['imageCouverture'])) {
            $images[] = $notice['imageCouverture'];
        }
        // Image 80px
        if(!empty($notice['imagetteCouverture'])) {
            $images[] = $notice['imagetteCouverture'];
        }
        return $images;
    }


    /**
     * Recherche de notices
     *
     * @param array $query_params : tableau cle/valeur des parametres a passer dans la requete
     * @param string $method : notices / ean
     *
     * @return []
     */
    public function search(array $query_params, $method = 'notices')
    {
        $this->renewAccessToken();
        if(empty($this->access_token)) {
            return [];
        }

        if(is_null($this->guzzle_client)) {
            $this->guzzle_client = new GuzzleHttp\Client();
        }


        $query_options = [];
        $query_options['headers'] = [
            'Accept' => '*/*',
            'Authorization' => 'Bearer '.$this->access_token,
        ];

        $query = $this->api_base_url;
        switch($method) {
            case 'ean' :
                $query.= '/notices/ean/';
                $query.= $query_params['ean'] ?? '';
                break;

            case 'notices' :
            default :
                $query.= '/notices/search?';
                $query_options['query'] = $query_params;
                break;
        }

        try {
            $response = $this->guzzle_client->request(
                'GET',
                $query,
                $query_options
            );
            $contents = json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            // handle exception or api errors.
            // print_r($e->getMessage());
            return [];
        }

        return $contents;
    }


    /**
     * Recuperation d'un token d'acces � l'API
     *
     * @return $access_token;
     */
    protected function renewAccessToken()
    {
        $remaining_token_time = $this->calcRemainingTokenTime();
        if ($remaining_token_time > 10) {
            return $this->access_token;
        }
        if(is_null($this->oidc_client)) {

           $this->oidc_client = new OpenIDConnectClient(
               $this->api_base_url,
               $this->client_id,
               $this->client_secret,
           );
           $this->oidc_client->providerConfigParam(['token_endpoint' => $this->api_token_url]);
           $this->oidc_client->addAuthParam(array('username'=> $this->client_user));
           $this->oidc_client->addAuthParam(array('password'=>$this->client_secret));
           $this->oidc_client->addScope(['roles']);
       }
       $ressource_owner_token = $this->oidc_client->requestResourceOwnerToken(true);
       if(is_object($ressource_owner_token) && property_exists($ressource_owner_token, 'access_token')) {
           $this->access_token = $ressource_owner_token->access_token;
       }
       return $this->access_token;
    }


    /**
     * Calcule la duree de validite restante du token d'acces
     * (0 si non indiqu�e)
     * @param string $token
     * @return boolean
     */
    protected function calcRemainingTokenTime()
    {
        $chunks = explode('.', $this->access_token);
        if( empty($chunks[1]) ) {
            return 0;
        }
        $payload = json_decode(base64_decode($chunks[1]), true);

        if(empty($payload) || empty($payload['exp'])) {
            return 0;
        }

        return $payload['exp'] - time();
    }
}

