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

    public function createFolder($folderName, $parentFolderId = 0) {

        $postData = [
            'session_id' => $this->sessionId,
            'folder_name' => $folderName,
            'folder_sub_parent' => $parentFolderId,
            'folder_is_public' => 0,
            'folder_public_upl' => 0,
            'folder_public_display' => 0,
            'folder_public_dnl' => 0,
        ];

        $response = $this->sendRequest('folder.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }


        return $response;
    }

    public function getFolderIdByPath($path)
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

    public function getFileIdByPath($path)
    {
        $postData = [
            'session_id' => $this->sessionId,
            'path' => $path,
        ];

        $response = $this->sendRequest('file/idbypath.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return $response['FileId'];
    }

    public function renameFile($fileId, $newName)
    {
        $postData = [
            'session_id' => $this->sessionId,
            'new_file_name' => $newName,
            'file_id' => $fileId,
        ];

        $response = $this->sendRequest('file/rename.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return true;
    }

    public function moveOrCopyFile($fileId, $destinationFolderId, $newName, $move, $overwrite = false)
    {
        $postData = [
            'session_id' => $this->sessionId,
            'move' => $move ? 'true': 'false',
            'src_file_id' => $fileId,
            'dst_folder_id' => $destinationFolderId,
            'overwrite_if_exists' => $overwrite ? 'true': 'false',
            'new_file_name' => $newName,
        ];

        $response = $this->sendRequest('file/move_copy.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return true;
    }

    public function deleteFolder($folderId)
    {
        $this->trashFolder($folderId);
        $this->removeFolderFromTrash($folderId);

        return true;
    }

    public function trashFolder($folderId) {
        $postData = [
            'session_id' => $this->sessionId,
            'folder_id' => $folderId,
        ];

        $response = $this->sendRequest('folder/trash.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return true;
    }

    public function removeFolderFromTrash($folderId) {
        $postData = [
            'session_id' => $this->sessionId,
            'folder_id' => $folderId,
        ];

        $response = $this->sendRequest("folder/remove.json", $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return true;
    }

    public function deleteFile($fileId) {
        $this->trashFile($fileId);
        $this->removeFileFromTrash($fileId);

        return true;
    }

    public function trashFile($fileId) {
        $postData = [
            'session_id' => $this->sessionId,
            'file_id' => $fileId,
        ];

        $response = $this->sendRequest('file/trash.json', $postData);

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return true;
    }

    public function removeFileFromTrash($fileId) {
        $response = $this->sendRequest("file.json/{$this->sessionId}/{$fileId}", false, 'Delete');

        if (false !== $errors = $this->hasErrors($response)) {
            throw new OpenDriveClientException("Error: {$errors['message']}");
        }

        return true;
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

    private function sendRequest($endPoint, $data = false, $customRequest = false)
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

        if ($customRequest) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
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