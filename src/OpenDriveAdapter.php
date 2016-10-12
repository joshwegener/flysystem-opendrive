<?php

namespace JoshWegener\FlysystemOpenDrive;

use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Adapter\Polyfill\StreamedTrait;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use JoshWegener\FlysystemOpenDrive\OpenDriveClient;

class OpenDriveAdapter implements AdapterInterface
{
    use NotSupportingVisibilityTrait;
    use StreamedTrait;

    const TYPE_FILE = 'file';
    const TYPE_DIRECTORY = 'dir';

    private $client;

    public function __construct(OpenDriveClient $client)
    {
        $this->client = $client;
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $fileId = $this->client->getFileIdByPath($path);
        $folderId = $this->client->getFolderIdByPath(dirname($newpath));

        return $this->client->moveOrCopyFile($fileId, $folderId, basename($newpath), true);
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $fileId = $this->client->getFileIdByPath($path);
        $folderId = $this->client->getFolderIdByPath(dirname($newpath));

        return $this->client->moveOrCopyFile($fileId, $folderId, basename($newpath), false);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $fileId = $this->client->getFileIdByPath($path);
        return $this->client->deleteFile($fileId);
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        $fileId = $this->client->getFolderIdByPath($dirname);
        return $this->client->deleteFolder($fileId);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        $parentFolderId = 0;
        $folderParts = explode('/', $dirname);
        $folderName = false;

        if (count($folderParts) != 1) {
            $folderName = array_pop($folderParts);
            $parentPath = implode("/", $folderParts);
            $parentFolderId = $this->client->getFolderIdByPath($parentPath);
        }

        $folder = $this->client->createFolder($folderName, $parentFolderId);

        $folder['path'] = $dirname;
        $folder['type'] = self::TYPE_DIRECTORY;
        $folder['timestamp'] = $folder['DateModified'];
        $folder['mimetype'] = null;
        $folder['size'] = null;

        return $folder;
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        try {
            $this->client->getFileIdByPath($path);
        } catch (OpenDriveClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directoryId = false;
        $results = [];

        if ('' == $directory || '/' == $directory) {
            $directory = '';
            $directoryId = 0;
        } else {
            $directoryId = $this->client->getFolderIdByPath($directory);
        }

        $response = $this->client->getContents($directoryId);

        foreach ($response['Files'] AS $file) {
            $path = '' == $directory ? $file['Name'] : "{$directory}/{$file['Name']}";

            $file['path'] = $path;
            $file['type'] = self::TYPE_FILE;
            $file['timestamp'] = $file['DateModified'];
            $file['mimetype'] = $file['Extension'];
            $file['size'] = $file['Size'];

            $results[] = $file;
        }

        if (isset($response['Folders'])) {
            foreach ($response['Folders'] AS $folder) {
                $path = '' == $directory ? $folder['Name'] : "{$directory}/{$folder['Name']}";

                $folder['path'] = $path;
                $folder['type'] = self::TYPE_DIRECTORY;
                $folder['timestamp'] = $folder['DateModified'];
                $folder['mimetype'] = null;
                $folder['size'] = null;

                $results[] = $folder;

                if ($recursive) {
                    $results = array_merge($results, $this->listContents($results, true));
                }
            }
        }

        return $results;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        /*
         * https://www.opendrive.com/wp-content/uploads/guides/OpenDrive_API_guide.pdf
         * https://laravel.com/docs/5.3/filesystem
         *
ole: 1 and 2
URL Structure: /file/info.json/{file_id}
Method: Get
Parameters:
file_id: string (required) – File ID.
session_id: string - Session ID.
sharing_id: string – Sharing ID

URL Structure: /folder/info.json/{session_id}/{folder_id}
Method: Get
Parameters:
session_id: string (required) - Session ID.
folder_id: string (required) - Folder ID.

         */
        // TODO: Implement getMetadata() method.
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        // TODO: Implement getSize() method.
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.
    }

    /**
     * Reads a file.
     *
     * @param string $path
     *
     * @return array|false
     *
     * @see League\Flysystem\ReadInterface::read()
     */
    public function read($path)
    {
        // TODO: Implement read() method.
    }

    public function write($pash, $contents, Config $config)
    {
        // TODO: Implement write() method.
    }

    public function update($pash, $contents, Config $config)
    {
        // TODO: Implement update() method.
    }
}
