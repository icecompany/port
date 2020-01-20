<?php
use Joomla\CMS\Table\Table;
defined('_JEXEC') or die;

/*
 * Таблица контактных данных об экспоненте
 */

class TableProjectsPersons extends Table
{
    var $id = null;
    var $exbID = null;
    var $fio = null;
    var $post = null;
    var $phone_work = null;
    var $phone_mobile = null;
    var $email = null;
    var $main = null;
    var $accreditation = null;
    var $building = null;
    var $comment = null;

    public function __construct(JDatabaseDriver $db)
    {
        parent::__construct('#__prj_exp_persons', 'id', $db);
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