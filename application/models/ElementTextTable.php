<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 **/
 
/**
 * @package Omeka
 * @author CHNM
 * @copyright Center for History and New Media, 2007-2008
 **/
class ElementTextTable extends Omeka_Db_Table
{
    
    protected $_alias = 'ie';
    
    public function getSelectForItem($itemId=null)
    {
        $select = $this->getSelect();
        $db = $this->getDb();
        
        // Join against the record_types table to only retrieve text for items.
        $select->joinInner(array('rty'=>$db->RecordType), 
            'rty.id = ' . $this->_alias . '.record_type_id AND rty.name = "Item"', array());
        
        $select->where( $this->_alias . '.record_id = ?', (int) $itemId);
        
        return $select;
    }
    
    /**
     * @todo Refactor getSelectForItem() to use this instead.
     * @param integer
     * @param string
     * @return Omeka_Db_Select
     **/
    public function getSelectForRecord($recordId, $recordType)
    {
        $select = $this->getSelect();
        $db = $this->getDb();
        
        // Join against the record_types table to only retrieve text for items.
        $select->joinInner(array('rty'=>$db->RecordType), 
            'rty.id = ' . $this->_alias . '.record_type_id AND rty.name = "' . (string)$recordType . '"', array());
        
        $select->where( $this->_alias . '.record_id = ?', (int) $recordId);
        
        return $select;
    }
    
    /**
     * Find all ElementText records for a given database record (Item, File, etc).
     * 
     * @param Omeka_Record
     * @return array
     **/
    public function findByRecord(Omeka_Record $record)
    {
        $select = $this->getSelectForRecord($record->id, get_class($record));
        return $this->fetchObjects($select);
    }
    
    /**
     * 
     * @param string
     * @return void
     **/
    protected function getRecordTypeId($recordTypeName)
    {
        // Cache the record type ID so we don't have to retrieve it every time.
        if(empty($this->_recordTypeId[$recordTypeName])) {
            $this->_recordTypeId[$recordTypeName] = $this->getDb()->getTable('RecordType')->findIdFromName($recordTypeName);
        }
        
        return $this->_recordTypeId[$recordTypeName];
    }
    
    /**
     * @see Element::saveTextFor()
     * @param integer
     * @param integer
     * @param integer
     * @return array Set of ItemsElements records, some of which may be new.
     **/
    public function findOrNewByItemAndElement($itemId, $elementId, $numToFind)
    {
        $itemRecordTypeId = (int) $this->getRecordTypeId('Item');
        
        $records = array();
        $select = $this->getSelectForItems($itemId);
        
        $select->where( $this->_alias . '.element_id = ?', $elementId);
        
        $select->limit($numToFind);
        
        $records = $this->fetchObjects($select);
        
        for ($i=0; $i < $numToFind; $i++) { 
            if(!$records[$i]) {
                $record = new ElementText;
                $record->record_id = $itemId;
                $record->element_id = $elementId;
                $record->record_type_id = $itemRecordTypeId;
                $records[$i] = $record;
            }
        }

        return $records;
    }
}
