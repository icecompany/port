<?php
use Joomla\CMS\Table\Table;
defined('_JEXEC') or die;

class TableProjectsExponents extends Table
{
    var $id = null;
    var $is_contractor = null;
    var $is_ndp = null;
    var $regID = null;
    var $regID_fact = null;
    var $tip = null;
    var $title_ru_full = null;
    var $title_ru_short = null;
    var $title_en = null;
    var $comment = null;
    var $user_id = null;

    public function __construct(JDatabaseDriver $db)
    {
        return parent::__construct('#__prj_exp', 'id', $db);
    }

    public function store($updateNulls = true)
    {
        return parent::store(true);
    }

    public function bind($src, $ignore = array())
    {
        foreach ($src as $field => $value)
        {
            if (isset($this->$field)) $this->$field = $value;
        }
        return parent::bind($src, $ignore);
    }
}