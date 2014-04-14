<?php

class Gallery_Update extends App_Update
{
    const VERSION = '0.3.0';
    
    public static function getClassName() { return 'Gallery_Update'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
                    
    public function update()
    {
        if ( $this->isVersionBelow( '0.1.0' ) ) {
            $this->_install();
        }
        if ( $this->isVersionBelow( '0.2.0' ) ) {

	    $tblEntry = Gallery_Entry::Table();
	    if ( !( $tblEntry->hasColumn( 'gal_dt_added' ) ))
    		$tblEntry->addColumn( 'gal_dt_added', 'DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL' );
	    if ( !( $tblEntry->hasColumn( 'gal_dt_modified' ) ))
    		$tblEntry->addColumn( 'gal_dt_modified', 'DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL' );
    	
	    $tblImage = Gallery_Image::Table();
	    if ( !( $tblImage->hasColumn( 'gali_dt_added' ) ))
    		$tblImage->addColumn( 'gali_dt_added', 'DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL' );
	    if ( !( $tblImage->hasColumn( 'gali_dt_modified' ) ))
    		$tblImage->addColumn( 'gali_dt_modified', 'DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL' );
	
        }
        if ( $this->isVersionBelow( '0.3.0' ) ) {
	    $tblImage = Gallery_Image::Table();
	    if ( !( $tblImage->hasColumn( 'gali_caption' ) ))
    		$tblImage->addColumn( 'gali_caption', 'VARCHAR(100) DEFAULT \'\' NOT NULL' );
        }
        $this->save( self::VERSION );
    }

    /** @return array */
    public static function getTables()
    {
        // no own tables
        return array( 'gallery_list', 'gallery_image' );
    }
    
    /** @return void */
    protected function _install()
    {
        // install gallery table
        if (!$this->getDbAdapterRead()->hasTable('gallery_entry')) {
            Sys_Io::out('Creating Gallery Table');
            $this->getDbAdapterWrite()->addTableSql('gallery_entry', '
                  `gal_id`            int(11)    NOT NULL AUTO_INCREMENT,
                  `gal_type_id`       int(11)    NOT NULL DEFAULT \'0\',
                  `gal_page_id`       int(11)    NOT NULL,
                  `gal_enabled`       int(2)     NOT NULL DEFAULT \'1\',
                  `gal_sort_order`    int(11)    NOT NULL DEFAULT \'0\',
                  KEY i_gal_page_id( gal_page_id ),
                  KEY i_gal_type_enabled( gal_type_id, gal_enabled )
            ', 'gal_id' );
        }
        // install gallery images table
        if (!$this->getDbAdapterRead()->hasTable('gallery_image')) {
            Sys_Io::out('Creating Gallery Images Table');

            $this->getDbAdapterWrite()->addTableSql('gallery_image', '
                  `gali_id`            int(11)       NOT NULL AUTO_INCREMENT,
                  `gali_entry_id`      int(11)       NOT NULL DEFAULT 0, -- leave 0 if galleries are not managed
                  `gali_page_id`       int(11)       NOT NULL DEFAULT 0, -- for separate pages of the images
                  
                  `gali_image`         VARCHAR(250)  NOT NULL DEFAULT \'\',
                  `gali_image_width`   int(11)       NOT NULL DEFAULT -1,
                  `gali_image_height`  int(11)       NOT NULL DEFAULT -1,

                  `gali_thumb`         VARCHAR(250)  NOT NULL DEFAULT \'\',
                  `gali_thumb_width`   int(11)       NOT NULL DEFAULT -1,
                  `gali_thumb_height`  int(11)       NOT NULL DEFAULT -1,

                  `gali_enabled`       int(2)     NOT NULL DEFAULT \'1\',
                  `gali_sort_order`    int(11)    NOT NULL DEFAULT \'0\',

                  KEY i_gali_page_id( gali_page_id ),
                  KEY i_gali_enabled( gali_enabled )
                ', 'gali_id' );
        }

    }
}