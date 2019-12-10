<?php
defined('_JEXEC') or die;
jimport('joomla.form.formrule');
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;

class JFormRuleCity extends JFormRule
{
    protected $regex = '^[0-9]+$';

    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        if (empty($value)) return false;
        return parent::test($element, $value, $group, $input, $form);
    }
}