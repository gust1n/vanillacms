/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	config.language = 'en';
	config.uiColor = '#ddd';
	//config.contentsCss = gdn.definition('WebRoot') + '/themes/default/design/style.css';
	config.toolbar = 'VCMSeditor';
	

   config.toolbar_VCMSeditor =
   [
       ['Source'],
       ['Cut','Copy','Paste','tokens'],
       ['Undo','Redo','-','Find','RemoveFormat','SpellChecker'],
       '/',
       
       ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
       ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
       ['Link','Unlink'],
       ['Image','Table','HorizontalRule','PageBreak'],
       '/',
       ['Format','FontSize'],
       ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
       ['TextColor','BGColor'],
       ['Maximize', 'ShowBlocks',]
   ];
};
