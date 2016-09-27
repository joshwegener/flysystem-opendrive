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
        // TODO: Implement rename() method.
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
        // TODO: Implement copy() method.
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
        // TODO: Implement delete() method.
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
        // TODO: Implement deleteDir() method.
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
        // TODO: Implement createDir() method.
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
        // TODO: Implement has() method.
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
            $directoryId = $this->client->getIdByPath($directory);
        }

        $response = $this->client->getContents($directoryId);

        foreach ($response['Files'] AS $file) {
            $path = '' == $directory ? $file['Name']: "{$directory}/{$file['Name']}";
            $flysystemMetadata = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $path);
            $flysystemMetadata->timestamp = $file['DateModified'];
            $flysystemMetadata->mimetype = $file['Extension'];
            $flysystemMetadata->size = $file['Size'];

            $results[] = $flysystemMetadata->toArray();
        }

        if (isset($response['Folders'])) {
            foreach ($response['Folders'] AS $folder) {
                $path = '' == $directory ? $folder['Name']: "{$directory}/{$folder['Name']}";
                $flysystemMetadata = new FlysystemMetadata(FlysystemMetadata::TYPE_DIRECTORY, $path);
                $flysystemMetadata->timestamp = $folder['DateModified'];
                $flysystemMetadata->mimetype = null;
                $flysystemMetadata->size = null;

                $results[] = $flysystemMetadata->toArray();

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
