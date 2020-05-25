<?php
use Joomla\CMS\Table\Table;
defined('_JEXEC') or die;

class TableProjectsContracts extends Table
{
    var $id = null;
    var $prjID = null;
    var $expID = null;
    var $managerID = null;
    var $parentID = null;
    var $dat = null;
    var $currency = null;
    var $isCoExp = null;
    var $status = null;
    var $doc_status = null;
    var $number = null;
    var $number_free = null;
    var $payerID = null;
    var $userID = null;
    var $no_exhibit = null;
    var $info_arrival = null;

    public function __construct(JDatabaseDriver $db)
    {
        parent::__construct('#__prj_contracts', 'id', $db);
    }

    public function load($keys = null, $reset = true)
    {
        return parent::load($keys, $reset);
    }

    public function bind($src, $ignore = array())
    {
        foreach ($src as $field => $value)
        {
            if (isset($this->$field)) $this->$field = $value;
        }
        return parent::bind($src, $ignore);
    }
    public function save($src, $orderingFilter = '', $ignore = '')
    {
        return parent::save($src, $orderingFilter, $ignore);
    }

    public function store($updateNulls = true)
    {
        return parent::store(true);
    }
}