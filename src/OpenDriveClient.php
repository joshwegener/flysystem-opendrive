<?php

namespace JoshWegener\FlysystemOpenDrive;

class OpenDriveClient
{
    public $apiUrl = 'https://dev.opendrive.com/api/v1/';
    private $sessionId;
    const POST = TRUE;
    const GET = FALSE;

    /**
     * OneDriveClient constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->createSession($username, $password);
    }

    private function createSession($username, $password)
    {
        // The data to send to the API
        $postData = array(
            'username' => $username,    //string (required) - Username.
            'passwd' => $password,      //string (required) - User password.
            'version' => '',            //string - Application version number (max 10).
            'partner_id' => ''          //string - Partner username  (Empty for OpenDrive)
        );

        $response = $this->sendRequest($postData, 'session/login.json', SELF::POST);

        print_r($response);
    }

    private function sendRequest($data, $endPoint, $post = false)
    {
        $ch = curl_init("{$this->apiUrl}/{$endPoint}");

        curl_setopt_array($ch, array(
            CURLOPT_POST => $post,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($data)
        ));

        $response = curl_exec($ch);

        if ($response === FALSE) {
            throw new OpenDriveClientException('Error when trying to run curl command.');
        }

        return json_decode($response, TRUE);
    }
}