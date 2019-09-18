<?php
/**
 * Таблица с историей статусов сделок менеджеров
 * @since 2.0.1-alpha.2
 * @copyright asharikov
 * @license asharikov
 */
use Joomla\CMS\Table\Table;
defined('_JEXEC') or die;

class TableProjectsManagers_stat extends Table
{
    var $id = null;
    var $dat = null;
    var $managerID = null;
    var $projectID = null;
    var $status_0 = null;
    var $status_1 = null;
    var $status_2 = null;
    var $status_3 = null;
    var $status_4 = null;
    var $status_7 = null;
    var $status_8 = null;
    var $status_9 = null;
    var $status_10 = null;
    var $exhibitors = null;
    var $plan = null;
    var $todos_expire = null;
    var $todos_plan = null;
    var $todos_future = null;
    var $todos_completed = null;
    var $sync_time = null;

    public function __construct(JDatabaseDriver $db)
    {
        parent::__construct('#__prj_managers_stat', 'id', $db);
    }

    public function store($updateNulls = true)
    {
        return parent::store(true);
    }

}