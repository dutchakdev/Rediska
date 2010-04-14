<?php

/**
 * @see Rediska_Command_Response_ValueAndScore
 */
require_once 'Rediska/Command/Response/ValueAndScore.php';

/**
 * Get all the members of the Sorted Set value at key
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name        Key name
 * @param integer $withScores  Return values with scores
 * @param integer $limit       Limit of elements
 * @param integer $offset      Offset (not using in sorting)
 * @param boolean $revert      Revert elements (not used in sorting)
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSortedSet extends Rediska_Command_Abstract
{
    protected $_version = '1.1';
    
    protected function _create($name, $withScores = false, $limit = null, $offset = null, $revert = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        if (!is_null($limit) && !is_integer($limit)) {
            throw new Rediska_Command_Exception("Limit must be integer");
        }

        if (is_null($offset)) {
            $offset = 0;
        } else if (!is_integer($offset)) {
            throw new Rediska_Command_Exception("Offset must be integer");
        }

        $start = $offset;

        if (is_null($limit)) {
            $end = -1;
        } else {
            $end = $offset + $limit - 1;
        }

        $command = array($revert ? 'ZREVRANGE' : 'ZRANGE',
                         "{$this->_rediska->getOption('namespace')}$name",
                         $start,
                         $end);

        if ($withScores) {
        	$command[] = 'WITHSCORES';
        }

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        $values = $responses[0];

        if ($this->withScores) {
        	$values = Rediska_Command_Response_ValueAndScore::combine($this->_rediska, $values);
        } else {
            foreach($values as &$value) {
        	   $value = $this->_rediska->unserialize($value);
            }
        }

        return $values;
    }
}