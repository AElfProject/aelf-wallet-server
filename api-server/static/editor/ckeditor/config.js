/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	config.language = 'zh-cn';
	config.toolbar_Basic =
	[
		['Format','Font','FontSize','Bold','TextColor','-','JustifyLeft','JustifyCenter','JustifyRight']
	];
	config.toolbar_Full =
	[
		['Source','Preview','-','Templates'],
		['Cut','Copy','Paste','PasteText','PasteWord','-','Print'],
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		['Blockquote','CreateDiv'],
		['FitWindow','ShowBlocks'],
		'/',
		['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
		['BulletedList','NumberedList'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
		['Link','Unlink','Anchor'],
		['Image','Flash','Table','Rule','SpecialChar'],
		'/',
		['Style','Format','Font','FontSize'],
		['TextColor','BGColor']
	];
	config.toolbar = 'Basic';

	config.filebrowserImageUploadUrl = 'upload_editor.asp';
};
