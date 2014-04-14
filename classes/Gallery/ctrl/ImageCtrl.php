<?php

class Gallery_ImageCtrl extends App_DbTableCtrl
{

    protected function _filterField($strFieldName, $strFieldValue) 
    {
//	Sys_Io::out( 'FILTERING:'. $strFieldName );
	
        switch ( $strFieldName )
        {
            case 'with_pages':
                $this->_select->joinInner( Cms_Page::TableName(), 'gali_page_id = pg_id' );
                $this->_selectCount->where( Cms_Page::TableName(), 'gali_page_id = pg_id' );
                break;
            case 'sort_order_less':
                $this->_select->where( 'gali_sort_order < ?', $strFieldValue);
                $this->_selectCount->where( 'gali_sort_order < ?', $strFieldValue);
                break;
            case 'sort_order_greater':
                $this->_select->where( 'gali_sort_order > ?', $strFieldValue);
                $this->_selectCount->where( 'gali_sort_order > ?', $strFieldValue);
                break;
            case 'imagename':
                $this->_select->where( 'gali_image LIKE ?',  '%/'.$strFieldValue.'.jpg' );
                $this->_selectCount->where( 'gali_image LIKE ?', '%/'.$strFieldValue.'.jpg' );
                break;
                
            case 'imagelike':
        	    $this->_select->where( 'gali_image LIKE ?', '%'.$strFieldValue.'%' );
        	    $this->_selectCount->where( 'gali_image LIKE ?', '%'.$strFieldValue.'%' );
        	    break;
            default:
                parent::_filterField($strFieldName, $strFieldValue);
        }
    
    }
    
    public function getlistAction()
    {
        parent::getlistAction();
    }
}
