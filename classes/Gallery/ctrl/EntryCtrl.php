<?php

class Gallery_EntryCtrl extends App_DbTableCtrl
{
    protected function  _joinTables() 
    {
        parent::_joinTables();

        $this->_select->joinInner( Cms_Page::TableName(), 'gal_page_id = pg_id' );
    }

    public function getAction()
    {
        if ( !$this->_hasParam('gal_id') ) {
            if ( $this->_hasParam('pg_slug') ) {
                // allow to find by slug
                $strLang = $this->_getParam( 'lang', '' );
                $objPage = Cms_Page::Table()->findBySlug(
                        $this->_getParam( 'pg_slug'), $strLang );
                if ( is_object( $objPage )) {
                    $selectGallery = Gallery_Entry::Table()
                        ->select()
                        ->where( 'gal_page_id = ? ', $objPage->getId() );
                    $objGallery = Gallery_Entry::Table()->fetchRow( $selectGallery );
                    if ( is_object( $objGallery ) ) {
                        $this->_identity  = $objGallery->getId();
                    }
                }
            }
        }
        parent::getAction();
        $this->view->max_size = ini_get(  'upload_max_filesize' );
    }

    public function setOrderAction()
    {
        $strImageIds = $this->_getParam( 'order' );
        $tblGalleryImage = Gallery_Image::Table();
        $nIterator = 0;

        foreach ( explode( '|', $strImageIds ) as $nImageId ) {
            $objImage = $tblGalleryImage->find( $nImageId )->current();
            if ( is_object( $objImage )) {
                $objImage->gali_sort_order = $nIterator;
                $objImage->save();
                $nIterator ++;
            }
        }
        $this->view->affected = $nIterator;
        $this->view->return = $this->_getParam( 'return' );
    }

    public function preprocessAction()
    {
	// empty action for manual thumbnail processing
        $this->view->return = $this->_getParam( 'return' );
        $this->view->gal_entry_id = $this->_getParam( 'gal_entry_id', 0);
        $this->view->gal_page_id = $this->_getParam( 'gal_page_id', 0 );

        if (count( $_FILES) > 0 ) {
            $strImageTempFile = $_FILES['gal_image_file']['tmp_name'];

            $strName = '';
            if ( !file_exists( $strImageTempFile ) ) {
                $this->view->lstErrors = array( $strName. ' : '
                  .$this->view->translate('File was not uploaded'));
                return;
            }
            
            if ( isset($_FILES['gal_image_file']['name'] ) )
                $strName =  $_FILES['gal_image_file']['name'];

            if ( !strstr( $_FILES[ 'gal_image_file' ]['type'], 'image/' )) {
                $this->view->lstErrors = array( $strName. ' : '
                  .$this->view->translate('Invalid file type'));
                return;
            }

            
            $strImagePath = '/cdn/preprocess/'. basename($strImageTempFile).'.jpg';
            list( $nImageWidth, $nImageHeight ) = getimagesize( $strImageTempFile );

            if ( $this->_hasParam( 'accept_max_height') ) {
                if ( $nImageHeight > $this->_getIntParam( 'accept_max_height') ) {
                     $this->view->lstErrors = array( $strName. ' : '
                             .$this->view->translate('Image height cannot exceed')
                             .' '.$this->_getIntParam( 'accept_max_height')
                             .', '.$this->view->translate( 'Now' ).': '
                             .$nImageHeight.' '.$this->view->translate('pixels')
                             );
                     return;
                }
            }
            if ( $this->_hasParam( 'accept_max_width') ) {
                if ( $nImageWidth > $this->_getIntParam( 'accept_max_width') ) {
                     $this->view->lstErrors = array( $strName. ' : '
                             .$this->view->translate('Image width cannot exceed')
                             .' '.$this->_getIntParam( 'accept_max_width')
                             .', '.$this->view->translate( 'Now' ).': '
                             .$nImageWidth.' '.$this->view->translate('pixels')
                             );
                     return;
                }
            }

            if ( $this->_hasParam( 'resize_to_max_width') || $this->_hasParam( 'resize_to_max_height') ) {
                
                if ( $nImageHeight > $this->_getIntParam( 'resize_to_max_height') ) {

                     $image = new App_ImageFile( 'file://'.$strImageTempFile );
                     $image->generateResampled( $strImagePath,
                             $this->_getIntParam( 'resize_to_max_width', 0),
                             $this->_getIntParam( 'resize_to_max_height', 0) );
                     
                     if ( !file_exists( CWA_APPLICATION_DIR.$strImagePath ) ) {
                         throw new App_Exception( 'Image file was not created' );
                     }
                     // return;
                     $image = new App_ImageFile( $strImagePath );
                     $this->view->file_width  = $image->getWidth();
                     $this->view->file_height = $image->getHeight();

                } else {
                    
                    move_uploaded_file( $strImageTempFile, CWA_APPLICATION_DIR.$strImagePath );
                    $this->view->file_width  = $nImageWidth;
                    $this->view->file_height = $nImageHeight;
                }
                
            } else {

                move_uploaded_file( $strImageTempFile, CWA_APPLICATION_DIR.$strImagePath );
                $this->view->file_width  = $nImageWidth;
                $this->view->file_height = $nImageHeight;
            }
            
            $this->view->file_path   = $strImagePath;
        }

    }


    public function addImageAction()
    {
        //  Sys_Debug::dumpDie( $_POST );
        if ( $this->_hasParam( 'file_path') ) {

            $tblGalleryImage = Gallery_Image::Table();
            $objImage = $tblGalleryImage->createRow();
            // $objImage->save();
            $objImage->gali_entry_id = $this->_getParam( 'gali_entry_id');
            $objImage->gali_page_id = $this->_getParam( 'gali_page_id');

            $strTempFile     = $this->_getParam( 'file_path');
            $nOriginalWidth  = $this->_getIntParam('file_width');
            $nOriginalHeight = $this->_getIntParam('file_height');
            $nDestWidth      = $this->_getIntParam('dest_width');
            $nDestHeight     = $this->_getIntParam('dest_height');

            $nCropX          = $this->_getIntParam('x');
            $nCropY          = $this->_getIntParam('y');
            $nCropWidth      = $this->_getIntParam('w');
            $nCropHeight     = $this->_getIntParam('h');

            $strPathOriginal = '/cdn/gallery/images/'.$objImage->gali_entry_id.'_'.mt_rand(1000,9999).'.jpg';
            $strPathThumb = '/cdn/gallery/thumbs/'.basename( $strPathOriginal );

            // make a copy of full-size image
            copy( CWA_APPLICATION_DIR. $strTempFile, CWA_APPLICATION_DIR . $strPathOriginal );

            $img = new App_ImageFile( $strTempFile ) ;
           
            // $img_r = imagecreatefromjpeg( CWA_APPLICATION_DIR. $strTempFile );


            $dst_r = ImageCreateTrueColor( $nDestWidth, $nDestHeight );
            imagecopyresampled($dst_r,
                     $img->getGdPtr(),0,0, $nCropX, $nCropY,
                    $nDestWidth,$nDestHeight,$nCropWidth,$nCropHeight);
            imagejpeg($dst_r, CWA_APPLICATION_DIR . $strPathThumb, 90);



            $objImage->gali_image = $strPathOriginal;
            $objImage->gali_image_width  = $nOriginalWidth;
            $objImage->gali_image_height = $nOriginalHeight;

            $objImage->gali_thumb = $strPathThumb;
            $objImage->gali_thumb_width =  $nDestWidth;
            $objImage->gali_thumb_height = $nDestHeight;
            $objImage->save( false );

        }
        $this->view->return = $this->_getParam( 'return' );
    }
}