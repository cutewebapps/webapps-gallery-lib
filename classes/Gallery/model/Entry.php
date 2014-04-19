<?php

class Gallery_Entry_Table extends DBx_Table
{
/**
 * database table name
 */
    protected $_name='gallery_entry';
/**
 * database table primary key
 */
    protected $_primary='gal_id';


    public function findByPageId( $nPageId )
    {
        $selectGallery = $this->select()->where( 'gal_page_id = ?', $nPageId );
        return $this->fetchRow( $selectGallery );
    }
    
    public function getArrPageSlugs()
    {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from( $this->_name )
                ->joinInner( Cms_Page::TableName(), 'gal_page_id = pg_id' );
        $lstGalleries = $this->fetchAll($select);
        //Sys_Debug::dump( $lstGalleries->getAsArray('pg_slug') );
        return $lstGalleries->getAsArray('pg_slug');
    }
}

class Gallery_Entry_List extends DBx_Table_Rowset
{
}

class Gallery_Entry_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array('gal_type_id', 'gal_page_id', 'gal_enabled') );
    }
}

class Gallery_Entry_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array( 'gal_type_id', 'gal_page_id', 'gal_enabled') );
    }
}


class Gallery_Entry extends DBx_Table_Row
{
    public static function getClassName() { return 'Gallery_Entry'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /** @return int */
    public function getPageId() { return $this->gal_page_id; }

    /** @return int */
    public function getTypeId() { return $this->gal_type_id; }

    /** @return boolean */
    public function isEnabled() { return $this->gal_enabled; }
    
    protected function _update()
    {
	$this->gal_dt_modified = date('Y-m-d H:i:s');
	parent::_update();
    }
    
    protected function _insert()
    {
	$this->gal_dt_added = date('Y-m-d H:i:s' );
	parent::_insert();
    }
    
    protected function _delete()
    {
        // remove images
        $selectImg = Gallery_Image::Table()->select()->where( 'gali_entry_id = ?', $this->gal_id );
        foreach ( Gallery_Image::Table()->fetchAll( $selectImg ) as $objRow ) $objRow->delete();
        
        //remove page
        $selectPage = Cms_Page::Table()->select()->where( 'pg_id = ?', $this->gal_page_id );
        $objPage = Cms_Page::Table()->fetchRow( $selectPage );
        $objPage->delete();
        
        parent::_delete();
    }

}