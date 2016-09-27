<?php

namespace JoshWegener\FlysystemOpenDrive;

use JoshWegener\FlysystemOpenDrive\OpenDriveClientException;

class OpenDriveClient
{
    public $apiUrl = 'https://dev.opendrive.com/api/v1';
    private $sessionId;

    /**
     * OneDriveClient constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->createSession($username, $password);
    }

    public function getIdByPath($path)
    {
        $postData = [
            'session_id' => $this->sessionId,
            'path' => $path,
        ];

        $response = $this->sendRequest('folder/idbypath.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return $response['FolderId'];
    }

    public function getContents($directoryId)
    {
        $response = $this->sendRequest("folder/list.json/{$this->sessionId}/{$directoryId}");

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return $response;
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

        $response = $this->sendRequest('session/login.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: Unable to connect to Cloud Service");
        }

        $this->sessionId = $response['SessionID'];
    }

    private function sendRequest($endPoint, $data = false)
    {
        $ch = curl_init("{$this->apiUrl}/{$endPoint}");

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            throw new OpenDriveClientException('Error when trying to run curl command.');
        }

        $response = json_decode($response, TRUE);

        //return $this->checkForErrors($response);
        return $response;
    }

    private function hasErrors($response)
    {
        if (true === isset($response['error'])) {
            return $response['error'];
        }
        return false;
    }

//    private function checkForErrors($response)
//    {
//        if (true === isset($response['error'])) {
//            throw new OpenDriveClientException("Unexpected error code: {$response['error']['code']}:{$response['error']['message']}");
//        }
//        return $response;
//    }
}