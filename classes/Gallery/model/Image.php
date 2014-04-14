<?php

class Gallery_Image_Table extends DBx_Table
{
/**
 * database table name
 */
    protected $_name='gallery_image';
/**
 * database table primary key
 */
    protected $_primary='gali_id';
}

class Gallery_Image_List extends DBx_Table_Rowset
{
}

class Gallery_Image_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array( 'gali_image', 'gali_thumb', 'gali_entry_id', 'gali_id',
            'gali_page_id', 'gali_enabled',
            'sort_order_greater', 'sort_order_less', 'imagename', 'with_pages' ) );
    }
}

class Gallery_Image_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array( 'gali_image', 'gali_thumb', 'gali_entry_id',
            'gali_page_id', 'gali_enabled') );
    }
}


class Gallery_Image extends DBx_Table_Row
{
    public static function getClassName() { return 'Gallery_Image'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /** @return int */
    public function getEntryId() { return $this->gali_entry_id; }

    /** @return int */
    public function getPageId() { return $this->gali_page_id; }

    /** @return boolean */
    public function isEnabled() { return $this->gali_enabled; }

    /** @return Cms_ImageFile */
    public function getImageObject()
    {
        return new Cms_ImageFile( $this->gali_image, $this->gali_image_width, $this->gali_image_height );
    }
    /** @return Cms_ImageFile */
    public function getThumbObject()
    {
        return new Cms_ImageFile( $this->gali_thumb, $this->gali_thumb_width, $this->gali_thumb_height );
    }
    
    protected function _update()
    {
	$this->gali_dt_modified = date('Y-m-d H:i:s');
	parent::_update();
    }
    
    protected function _insert()
    {
        $this->gali_sort_order = Gallery_Image::Table()->getIterator( 'gali_sort_order');
	$this->gali_dt_added = date('Y-m-d H:i:s' );
	parent::_update();
    }
}
