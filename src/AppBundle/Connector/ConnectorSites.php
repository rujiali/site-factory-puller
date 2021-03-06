<?php
/**
 * @file
 *   Connector site api file.
 */
namespace AppBundle\Connector;

use AppBundle\Connector\Connector;

/**
 * Class Connector
 */
class ConnectorSites
{
    const VERSION = '/api/v1/sites/';

    protected $connector;

    /**
     * Connector constructor.
     *
     * @param \AppBundle\Connector\Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Clear site cache.
     *
     * @return mixed|string
     */
    public function clearCache()
    {
        if ($this->connector->getURL() === null) {
            return 'Cannot find site URL from configuration.';
        }
        $url = $this->connector->getURL().self::VERSION.$this->connector->getSiteID().'/cache-clear';
        $params = [];
        $response = $this->connector->connecting($url, $params, 'POST');

        return $response;
    }

    /**
     * Create backup for specific site in site factory.
     *
     * @param string $label      Backup label.
     * @param array  $components Backup components.
     *
     * @return integer
     *   Task ID.
     */
    public function createBackup($label, $components)
    {
        if ($this->connector->getURL() === null) {
            return 'Cannot find site URL from configuration.';
        }
        $url = $this->connector->getURL().self::VERSION.$this->connector->getSiteID().'/backup';
        $params = [
            'label' => $label,
            'components' => $components,
        ];

        $response = $this->connector->connecting($url, $params, 'POST');

        // @codingStandardsIgnoreStart
        if (isset($response->task_id)) {
            return $response->task_id;
            // @codingStandardsIgnoreEnd
        }

        return $response;
    }

    /**
     * Delete backup.
     *
     * @param int    $backupId       BackupId.
     * @param string $callbackUrl    Callback URL.
     * @param string $callbackMethod Callback method.
     * @param string $callerData     Caller data in JSON format.
     *
     * @return mixed|string
     */
    public function deleteBackup($backupId, $callbackUrl, $callbackMethod, $callerData)
    {
        if ($this->connector->getURL() === null) {
            return 'Cannot find site URL from configuration.';
        }
        $url = $this->connector->getURL().self::VERSION.$this->connector->getSiteID().'/backups/'.$backupId;
        $params = [
            'callback_url' => $callbackUrl,
            'callback_method' => $callbackMethod,
            'callerData' => $callerData,
        ];

        $response = $this->connector->connecting($url, $params, 'DELETE');

        // @codingStandardsIgnoreStart
        if (isset($response->task_id)) {
            return $response->task_id;
            // @codingStandardsIgnoreEnd
        }

        return $response;
    }

    /**
     * Get backup URL.
     *
     * @param string $backupId Backup ID.
     *
     * @return string
     */
    public function getBackupURL($backupId)
    {
        if ($this->connector->getURL() === null) {
            return 'Cannot find site URL from configuration.';
        }
        $url = $this->connector->getURL().self::VERSION.$this->connector->getSiteID().'/backups/'.$backupId.'/url';
        $params = [];
        $response = $this->connector->connecting($url, $params, 'GET');
        if (isset($response->url)) {
            return $response->url;
        }

        return $response;
    }

    /**
     * Get backup URL for latest backup.
     *
     * @return string
     */
    public function getLatestBackupURL()
    {
        // Find out the latest backup ID.
        $backups = $this->listBackups();
        if (is_array($backups)) {
            if (!empty($backups)) {
                $backupId = $backups[0]->id;

                return $this->getBackupURL($backupId);
            }

            return 'There is no backup available.';
        }

        return $backups;
    }

    /**
     * Get site details
     *
     * @param int $siteId Site ID.
     *
     * @return mixed|string
     */
    public function getSiteDetails($siteId)
    {
        if ($this->connector->getURL() === null) {
            return 'Cannot find site URL from configuration.';
        }
        $url = $this->connector->getURL().self::VERSION.$siteId;
        $params = [];
        $response = $this->connector->connecting($url, $params, 'GET');

        return $response;
    }

    /**
     * List all backups in for specific site in site factory.
     *
     * @return mixed
     */
    public function listBackups()
    {
        if ($this->connector->getURL() === null) {
            return 'Cannot find site URL from configuration.';
        }
        $url = $this->connector->getURL().self::VERSION.$this->connector->getSiteID().'/backups';
        $params = [];
        $response = $this->connector->connecting($url, $params, 'GET');

        if (isset($response->backups)) {
            return $response->backups;
        }

        return $response;
    }

    /**
     * Get the last backup timestamp.
     *
     * @return mixed|string
     */
    public function getLastBackupTime()
    {
        $backups = $this->listBackups();
        if (is_array($backups)) {
            if (!empty($backups)) {
                $timestamp = $backups[0]->timestamp;

                return $timestamp;
            }

            return 'There is no backup available.';
        }

        return $backups;
    }

    /**
     * Indicate when required backup is generated.
     *
     * @param string $label Backup label.
     *
     * @return bool
     */
    public function backupSuccessIndicator($label)
    {
        $backups = $this->listBackups();
        if (is_array($backups)) {
            if (!empty($backups)) {
                if ($backups[0]->label === $label) {
                    return true;
                }

                return false;
            }

            return false;
        }
    }

    /**
     * Wait for the backup is generated.
     *
     * @param string $label Backup label.
     *
     * @return bool
     */
    public function waitBackup($label)
    {
        while (!$this->backupSuccessIndicator($label)) {
            sleep(60);
        }

        return true;
    }

    /**
     * List sites.
     *
     * @param int     $limit  Limit number.
     * @param int     $page   Page number.
     * @param boolean $canary No value necessary.
     *
     * @return string
     */
    public function listSites($limit, $page, $canary = false)
    {
        if ($this->connector->getURL() === null) {
            return 'Cannot find site URL from configuration.';
        }
        $params = [
          'limit' => $limit,
          'page' => $page,
          'canary' => $canary,
        ];
        $url = $this->connector->getURL().self::VERSION;

        $response = $this->connector->connecting($url, $params, 'GET');

        if (isset($response->sites)) {
            return $response->sites;
        }

        return $response;
    }
}
