<?php
/**
 * UserTrackingManager handles tracking users from client to client
 *
 * PHP version 5
 *
 * @category UserTrackingManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * UserTrackingManager handles tracking users from client to client
 *
 * @category UserTrackingManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class UserTrackingManager extends FOGManagerController
{
    /**
     * The base table name.
     *
     * @var string
     */
    public $tablename = 'dirCleaner';
    /**
     * Install our table.
     *
     * @return bool
     */
    public function install()
    {
        $this->uninstall();
        $sql = Schema::createTable(
            $this->tablename,
            true,
            [
                'dcID',
                'dcPath'
            ],
            [
                'INTEGER',
                'LONGTEXT'
            ],
            [
                false,
                false
            ],
            [
                false,
                false
            ],
            [
                'dcID',
                'dcPath'
            ],
            'MyISAM',
            'utf8',
            'dcID',
            'dcID'
        );
        return self::$DB->query($sql);
    }
}
