<?php
/* Copyright (C) 2021 EOXIA <dev@eoxia.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/digiriskdolibarr_function.lib.php
 * \ingroup digiriskdolibarr
 * \brief   Library files with common functions for Digiriskdolibarr
 */

/**
 *  Show photos of an object (nbmax maximum), into several columns
 *
 *  @param		string	$modulepart		'product', 'ticket', ...
 *  @param      string	$sdir        	Directory to scan (full absolute path)
 *  @param      int		$size        	0=original size, 1='small' use thumbnail if possible
 *  @param      int		$nbmax       	Nombre maximum de photos (0=pas de max)
 *  @param      int		$nbbyrow     	Number of image per line or -1 to use div. Used only if size=1.
 * 	@param		int		$showfilename	1=Show filename
 * 	@param		int		$showaction		1=Show icon with action links (resize, delete)
 * 	@param		int		$maxHeight		Max height of original image when size='small' (so we can use original even if small requested). If 0, always use 'small' thumb image.
 * 	@param		int		$maxWidth		Max width of original image when size='small'
 *  @param      int     $nolink         Do not add a href link to view enlarged imaged into a new tab
 *  @param      int     $notitle        Do not add title tag on image
 *  @param		int		$usesharelink	Use the public shared link of image (if not available, the 'nophoto' image will be shown instead)
 *  @return     string					Html code to show photo. Number of photos shown is saved in this->nbphoto
 */
function digirisk_show_photos($modulepart, $sdir, $size = 0, $nbmax = 0, $nbbyrow = 5, $showfilename = 0, $showaction = 0, $maxHeight = 120, $maxWidth = 160, $nolink = 0, $notitle = 0, $usesharelink = 0, $subdir, $object)
{
	global $conf, $user, $langs;

	include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
	include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

	$sortfield = 'position_name';
	$sortorder = 'desc';

	$dir = $sdir.'/';
	$pdir = $subdir . '/';

	$dir .= get_exdir(0, 0, 0, 0, $object, $modulepart);
	$pdir .= get_exdir(0, 0, 0, 0, $object, $modulepart);

	// Defined relative dir to DOL_DATA_ROOT
	$relativedir = '';
	if ($dir)
	{
		$relativedir = preg_replace('/^'.preg_quote(DOL_DATA_ROOT, '/').'/', '', $dir);
		$relativedir = preg_replace('/^[\\/]/', '', $relativedir);
		$relativedir = preg_replace('/[\\/]$/', '', $relativedir);
	}

	$dirthumb = $dir.'thumbs/';
	$pdirthumb = $pdir.'thumbs/';

	$return = '<!-- Photo -->'."\n";
	$nbphoto = 0;

	$filearray = dol_dir_list($dir, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ?SORT_DESC:SORT_ASC), 1);
	completeFileArrayWithDatabaseInfo($filearray, $relativedir);
	if (count($filearray))
	{

		if ($sortfield && $sortorder)
		{
			$filearray = dol_sort_array($filearray, $sortfield, $sortorder);
		}

		foreach ($filearray as $key => $val)
		{
			$photo = '';
			$file = $val['name'];

			//if (! utf8_check($file)) $file=utf8_encode($file);	// To be sure file is stored in UTF8 in memory

			//if (dol_is_file($dir.$file) && image_format_supported($file) >= 0)
			if (image_format_supported($file) >= 0)
			{
				$nbphoto++;
				$photo = $file;
				$viewfilename = $file;

				if ($size == 1 || $size == 'small') {   // Format vignette
					// Find name of thumb file
					$photo_vignette = basename(getImageFileNameForSize($dir.$file, '_small'));
					if (!dol_is_file($dirthumb.$photo_vignette)) $photo_vignette = '';

					// Get filesize of original file
					$imgarray = dol_getImageSize($dir.$photo);

					if ($nbbyrow > 0)
					{
						if ($nbphoto == 1) $return .= '<table class="valigntop center centpercent" style="border: 0; padding: 2px; border-spacing: 2px; border-collapse: separate;">';

						if ($nbphoto % $nbbyrow == 1) $return .= '<tr class="center valignmiddle" style="border: 1px">';
						$return .= '<td style="width: '.ceil(100 / $nbbyrow).'%" class="photo">';
					}
					elseif ($nbbyrow < 0) $return .= '<div class="inline-block">';

					$return .= "\n";

					$relativefile = preg_replace('/^\//', '', $pdir.$photo);
					if (empty($nolink))
					{
						$urladvanced = getAdvancedPreviewUrl($modulepart, $relativefile, 0, 'entity='.$object->entity);
						if ($urladvanced) $return .= '<a href="'.$urladvanced.'">';
						else $return .= '<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$object->entity.'&file='.urlencode($pdir.$photo).'" class="aphoto" target="_blank">';
					}

					// Show image (width height=$maxHeight)
					// Si fichier vignette disponible et image source trop grande, on utilise la vignette, sinon on utilise photo origine
					$alt = $langs->transnoentitiesnoconv('File').': '.$relativefile;
					$alt .= ' - '.$langs->transnoentitiesnoconv('Size').': '.$imgarray['width'].'x'.$imgarray['height'];
					if ($notitle) $alt = '';

					if ($usesharelink)
					{
						if ($val['share'])
						{
							if (empty($maxHeight) || $photo_vignette && $imgarray['height'] > $maxHeight)
							{
								$return .= '<!-- Show original file (thumb not yet available with shared links) -->';
								$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?hashp='.urlencode($val['share']).'" title="'.dol_escape_htmltag($alt).'">';
							}
							else {
								$return .= '<!-- Show original file -->';
								$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?hashp='.urlencode($val['share']).'" title="'.dol_escape_htmltag($alt).'">';
							}
						}
						else
						{
							$return .= '<!-- Show nophoto file (because file is not shared) -->';
							$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/public/theme/common/nophoto.png" title="'.dol_escape_htmltag($alt).'">';
						}
					}
					else
					{
						if (empty($maxHeight) || $photo_vignette && $imgarray['height'] > $maxHeight)
						{
							$return .= '<!-- Show thumb -->';
							$return .= '<img class="photo"  width="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$object->entity.'&file='.urlencode($pdirthumb.$photo_vignette).'" title="'.dol_escape_htmltag($alt).'">';
						}
						else {
							$return .= '<!-- Show original file -->';
							$return .= '<img class="photo photowithmargin" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$object->entity.'&file='.urlencode($pdir.$photo).'" title="'.dol_escape_htmltag($alt).'">';
						}
					}

					if (empty($nolink)) $return .= '</a>';
					$return .= "\n";

					if ($showfilename) $return .= '<br>'.$viewfilename;
					if ($showaction)
					{
						$return .= '<br>';
						// On propose la generation de la vignette si elle n'existe pas et si la taille est superieure aux limites
						if ($photo_vignette && (image_format_supported($photo) > 0) && ($object->imgWidth > $maxWidth || $object->imgHeight > $maxHeight))
						{
							$return .= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=addthumb&amp;file='.urlencode($pdir.$viewfilename).'">'.img_picto($langs->trans('GenerateThumb'), 'refresh').'&nbsp;&nbsp;</a>';
						}
						// Special cas for product
						if ($modulepart == 'product' && ($user->rights->produit->creer || $user->rights->service->creer))
						{
							// Link to resize
							$return .= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('produit|service').'&id='.$object->id.'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"), 'resize', '').'</a> &nbsp; ';

							// Link to delete
							$return .= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
							$return .= img_delete().'</a>';
						}
					}
					$return .= "\n";

					if ($nbbyrow > 0)
					{
						$return .= '</td>';
						if (($nbphoto % $nbbyrow) == 0) $return .= '</tr>';
					}
					elseif ($nbbyrow < 0) $return .= '</div>';
				}

				if (empty($size)) {     // Format origine
					$return .= '<img class="photo photowithmargin" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$object->entity.'&file='.urlencode($pdir.$photo).'">';

					if ($showfilename) $return .= '<br>'.$viewfilename;
					if ($showaction)
					{
						// Special case for product
						if ($modulepart == 'product' && ($user->rights->produit->creer || $user->rights->service->creer))
						{
							// Link to resize
							$return .= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('produit|service').'&id='.$object->id.'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"), 'resize', '').'</a> &nbsp; ';

							// Link to delete
							$return .= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
							$return .= img_delete().'</a>';
						}
					}
				}

				// On continue ou on arrete de boucler ?
				if ($nbmax && $nbphoto >= $nbmax) break;
			}
		}

		if ($size == 1 || $size == 'small')
		{
			if ($nbbyrow > 0)
			{
				// Ferme tableau
				while ($nbphoto % $nbbyrow)
				{
					$return .= '<td style="width: '.ceil(100 / $nbbyrow).'%">&nbsp;</td>';
					$nbphoto++;
				}

				if ($nbphoto) $return .= '</table>';
			}
		}
	}

	$object->nbphoto = $nbphoto;
	return $return;
}

/**
 *      Return a string to show the box with list of available documents for object.
 *      This also set the property $this->numoffiles
 *
 *      @param      string				$modulepart         Module the files are related to ('propal', 'facture', 'facture_fourn', 'mymodule', 'mymodule:nameofsubmodule', 'mymodule_temp', ...)
 *      @param      string				$modulesubdir       Existing (so sanitized) sub-directory to scan (Example: '0/1/10', 'FA/DD/MM/YY/9999'). Use '' if file is not into subdir of module.
 *      @param      string				$filedir            Directory to scan
 *      @param      string				$urlsource          Url of origin page (for return)
 *      @param      int|string[]        $genallowed         Generation is allowed (1/0 or array list of templates)
 *      @param      int					$delallowed         Remove is allowed (1/0)
 *      @param      string				$modelselected      Model to preselect by default
 *      @param      integer				$allowgenifempty	Allow generation even if list of template ($genallowed) is empty (show however a warning)
 *      @param      integer				$forcenomultilang	Do not show language option (even if MAIN_MULTILANGS defined)
 *      @param      int					$iconPDF            Deprecated, see getDocumentsLink
 * 		@param		int					$notused	        Not used
 * 		@param		integer				$noform				Do not output html form tags
 * 		@param		string				$param				More param on http links
 * 		@param		string				$title				Title to show on top of form. Example: '' (Default to "Documents") or 'none'
 * 		@param		string				$buttonlabel		Label on submit button
 * 		@param		string				$codelang			Default language code to use on lang combo box if multilang is enabled
 * 		@param		string				$morepicto			Add more HTML content into cell with picto
 *      @param      Object              $object             Object when method is called from an object card.
 *      @param		int					$hideifempty		Hide section of generated files if there is no file
 *      @param      string              $removeaction       (optional) The action to remove a file
 * 		@return		string              					Output string with HTML array of documents (might be empty string)
 */
function digiriskshowdocuments($modulepart, $modulesubdir, $filedir, $urlsource, $genallowed, $delallowed = 0, $modelselected = '', $allowgenifempty = 1, $forcenomultilang = 0, $notused = 0, $noform = 0, $param = '', $title = '', $buttonlabel = '', $codelang = '', $morepicto = '', $object = null, $hideifempty = 0, $removeaction = 'remove_file')
{
	global $db, $langs, $conf, $user, $hookmanager, $form;

	if (!is_object($form)) $form = new Form($this->db);

	include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	// Add entity in $param if not already exists
	if (!preg_match('/entity\=[0-9]+/', $param)) {
		$param .= ($param ? '&' : '').'entity='.(!empty($object->entity) ? $object->entity : $conf->entity);
	}

	$hookmanager->initHooks(array('formfile'));

	// Get list of files
	$file_list = null;
	if (!empty($filedir))
	{
		$file_list = dol_dir_list($filedir, 'files', 0, '', '(\.meta|_preview.*.*\.png)$', 'date', SORT_DESC);
	}
	if ($hideifempty && empty($file_list)) return '';

	$out = '';
	$forname = 'builddoc';
	$headershown = 0;
	$showempty = 0;

	$out .= "\n".'<!-- Start show_document -->'."\n";

	$titletoshow = $langs->trans("Documents");
	if (!empty($title)) $titletoshow = ($title == 'none' ? '' : $title);

	// Show table
	if ($genallowed)
	{
		$submodulepart = $modulepart;
		// modulepart = 'nameofmodule' or 'nameofmodule:NameOfObject'
		$tmp = explode(':', $modulepart);
		if (!empty($tmp[1])) {
			$modulepart = $tmp[0];
			$submodulepart = $tmp[1];
		}

		// For normalized external modules.
		$file = dol_buildpath('/'.$modulepart.'/core/modules/'.$modulepart.'/digiriskdocuments/'.strtolower($submodulepart).'/modules_'.strtolower($submodulepart).'.php', 0);
		include_once $file;

		$class = 'ModeleODT'.$submodulepart;

		if (class_exists($class))
		{
			$modellist = call_user_func($class.'::liste_modeles', $db);
		}
		else
		{
			dol_print_error($db, "Bad value for modulepart '".$modulepart."' in showdocuments");
			return -1;
		}

		// Set headershown to avoid to have table opened a second time later
		$headershown = 1;

		if (empty($buttonlabel)) $buttonlabel = $langs->trans('Generate');

		if ($conf->browser->layout == 'phone') $urlsource .= '#'.$forname.'_form'; // So we switch to form after a generation
		if (empty($noform)) $out .= '<form action="'.$urlsource.(empty($conf->global->MAIN_JUMP_TAG) ? '' : '#builddoc').'" id="'.$forname.'_form" method="post">';
		$out .= '<input type="hidden" name="action" value="builddoc">';
		$out .= '<input type="hidden" name="token" value="'.newToken().'">';

		$out .= load_fiche_titre($titletoshow, '', '');
		$out .= '<div class="div-table-responsive-no-min">';
		$out .= '<table class="liste formdoc noborder centpercent">';

		$out .= '<tr class="liste_titre">';

		$addcolumforpicto = ($delallowed || $printer || $morepicto);
		$colspan = (3 + ($addcolumforpicto ? 1 : 0)); $colspanmore = 0;

		$out .= '<th colspan="'.$colspan.'" class="formdoc liste_titre maxwidthonsmartphone center">';

		// Model
		if (!empty($modellist))
		{
			asort($modellist);
			$out .= '<span class="hideonsmartphone">'.$langs->trans('Model').' </span>';
			if (is_array($modellist) && count($modellist) == 1)    // If there is only one element
			{
				$arraykeys = array_keys($modellist);
				$modelselected = $arraykeys[0];
			}
			$morecss = 'maxwidth200';
			if ($conf->browser->layout == 'phone') $morecss = 'maxwidth100';
			$out .= $form->selectarray('model', $modellist, $modelselected, $showempty, 0, 0, '', 0, 0, 0, '', $morecss);
			if ($conf->use_javascript_ajax)
			{
				$out .= ajax_combobox('model');
			}
		}
		else
		{
			$out .= '<div class="float">'.$langs->trans("Files").'</div>';
		}

		// Language code (if multilang)
		if (($allowgenifempty || (is_array($modellist) && count($modellist) > 0)) && $conf->global->MAIN_MULTILANGS && !$forcenomultilang && (!empty($modellist) || $showempty))
		{
			include_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
			$formadmin = new FormAdmin($this->db);
			$defaultlang = $codelang ? $codelang : $langs->getDefaultLang();
			$morecss = 'maxwidth150';
			if ($conf->browser->layout == 'phone') $morecss = 'maxwidth100';
			$out .= $formadmin->select_language($defaultlang, 'lang_id', 0, null, 0, 0, 0, $morecss);
		}
		else
		{
			$out .= '&nbsp;';
		}

		// Button
		$genbutton = '<input class="button buttongen" id="'.$forname.'_generatebutton" name="'.$forname.'_generatebutton"';
		$genbutton .= ' type="submit" value="'.$buttonlabel.'"';
		if (!$allowgenifempty && !is_array($modellist) && empty($modellist)) $genbutton .= ' disabled';
		$genbutton .= '>';
		if ($allowgenifempty && !is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid')
		{
			$langs->load("errors");
			$genbutton .= ' '.img_warning($langs->transnoentitiesnoconv("WarningNoDocumentModelActivated"));
		}
		if (!$allowgenifempty && !is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid') $genbutton = '';
		if (empty($modellist) && !$showempty && $modulepart != 'unpaid') $genbutton = '';
		$out .= $genbutton;
		$out .= '</th>';

		if (!empty($hookmanager->hooks['formfile']))
		{
			foreach ($hookmanager->hooks['formfile'] as $module)
			{
				if (method_exists($module, 'formBuilddocLineOptions'))
				{
					$colspanmore++;
					$out .= '<th></th>';
				}
			}
		}
		$out .= '</tr>';

		// Execute hooks
		$parameters = array('colspan'=>($colspan + $colspanmore), 'socid'=>(isset($GLOBALS['socid']) ? $GLOBALS['socid'] : ''), 'id'=>(isset($GLOBALS['id']) ? $GLOBALS['id'] : ''), 'modulepart'=>$modulepart);
		if (is_object($hookmanager))
		{
			$reshook = $hookmanager->executeHooks('formBuilddocOptions', $parameters, $GLOBALS['object']);
			$out .= $hookmanager->resPrint;
		}
	}

	// Get list of files
	if (!empty($filedir))
	{
		$link_list = array();
		if (is_object($object))
		{
			require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
			$link = new Link($db);
			$sortfield = $sortorder = null;
			$res = $link->fetchAll($link_list, $object->element, $object->id, $sortfield, $sortorder);
		}

		$out .= '<!-- html.formfile::showdocuments -->'."\n";

		// Show title of array if not already shown
		if ((!empty($file_list) || !empty($link_list) || preg_match('/^massfilesarea/', $modulepart))
			&& !$headershown)
		{
			$headershown = 1;
			$out .= '<div class="titre">'.$titletoshow.'</div>'."\n";
			$out .= '<div class="div-table-responsive-no-min">';
			$out .= '<table class="noborder centpercent" id="'.$modulepart.'_table">'."\n";
		}

		// Loop on each file found
		if (is_array($file_list))
		{
			foreach ($file_list as $file)
			{
				// Define relative path for download link (depends on module)
				$relativepath = $file["name"]; // Cas general
				if ($modulesubdir) $relativepath = $modulesubdir."/".$file["name"]; // Cas propal, facture...

				$out .= '<tr class="oddeven">';

				$documenturl = DOL_URL_ROOT.'/document.php';
				if (isset($conf->global->DOL_URL_ROOT_DOCUMENT_PHP)) $documenturl = $conf->global->DOL_URL_ROOT_DOCUMENT_PHP; // To use another wrapper

				// Show file name with link to download
				$out .= '<td class="minwidth200">';
				$out .= '<a class="documentdownload paddingright" href="'.$documenturl.'?modulepart='.$modulepart.'&amp;file='.urlencode($relativepath).($param ? '&'.$param : '').'"';

				$mime = dol_mimetype($relativepath, '', 0);
				if (preg_match('/text/', $mime)) $out .= ' target="_blank"';
				$out .= '>';
				$out .= img_mime($file["name"], $langs->trans("File").': '.$file["name"]);
				$out .= dol_trunc($file["name"], 150);
				$out .= '</a>'."\n";
				$out .= '</td>';

				// Show file size
				$size = (!empty($file['size']) ? $file['size'] : dol_filesize($filedir."/".$file["name"]));
				$out .= '<td class="nowrap right">'.dol_print_size($size, 1, 1).'</td>';

				// Show file date
				$date = (!empty($file['date']) ? $file['date'] : dol_filemtime($filedir."/".$file["name"]));
				$out .= '<td class="nowrap right">'.dol_print_date($date, 'dayhour', 'tzuser').'</td>';

				if ($delallowed || $morepicto)
				{
					$out .= '<td class="right nowraponall">';
					if ($delallowed)
					{
						$tmpurlsource = preg_replace('/#[a-zA-Z0-9_]*$/', '', $urlsource);
						$out .= '<a href="'.$tmpurlsource.((strpos($tmpurlsource, '?') === false) ? '?' : '&amp;').'action='.$removeaction.'&amp;file='.urlencode($relativepath);
						$out .= ($param ? '&amp;'.$param : '');
						$out .= '">'.img_picto($langs->trans("Delete"), 'delete').'</a>';
					}
					if ($morepicto)
					{
						$morepicto = preg_replace('/__FILENAMEURLENCODED__/', urlencode($relativepath), $morepicto);
						$out .= $morepicto;
					}
					$out .= '</td>';
				}

				if (is_object($hookmanager))
				{
					$parameters = array('colspan'=>($colspan + $colspanmore), 'socid'=>(isset($GLOBALS['socid']) ? $GLOBALS['socid'] : ''), 'id'=>(isset($GLOBALS['id']) ? $GLOBALS['id'] : ''), 'modulepart'=>$modulepart, 'relativepath'=>$relativepath);
					$res = $hookmanager->executeHooks('formBuilddocLineOptions', $parameters, $file);
					if (empty($res))
					{
						$out .= $hookmanager->resPrint; // Complete line
						$out .= '</tr>';
					}
					else
					{
						$out = $hookmanager->resPrint; // Replace all $out
					}
				}
			}
		}
		// Loop on each link found
		if (is_array($link_list))
		{
			$colspan = 2;

			foreach ($link_list as $file)
			{
				$out .= '<tr class="oddeven">';
				$out .= '<td colspan="'.$colspan.'" class="maxwidhtonsmartphone">';
				$out .= '<a data-ajax="false" href="'.$file->url.'" target="_blank">';
				$out .= $file->label;
				$out .= '</a>';
				$out .= '</td>';
				$out .= '<td class="right">';
				$out .= dol_print_date($file->datea, 'dayhour');
				$out .= '</td>';
				if ($delallowed || $printer || $morepicto) $out .= '<td></td>';
				$out .= '</tr>'."\n";
			}
		}

		if (count($file_list) == 0 && count($link_list) == 0 && $headershown)
		{
			$out .= '<tr><td colspan="'.(3 + ($addcolumforpicto ? 1 : 0)).'" class="opacitymedium">'.$langs->trans("None").'</td></tr>'."\n";
		}
	}

	if ($headershown)
	{
		// Affiche pied du tableau
		$out .= "</table>\n";
		$out .= "</div>\n";
		if ($genallowed)
		{
			if (empty($noform)) $out .= '</form>'."\n";
		}
	}
	$out .= '<!-- End show_document -->'."\n";

	return $out;
}

/**
 *	Show HTML header HTML + BODY + Top menu + left menu + DIV
 *
 * @param 	string 	$head				Optionnal head lines
 * @param 	string 	$title				HTML title
 * @param	string	$help_url			Url links to help page
 * 		                            	Syntax is: For a wiki page: EN:EnglishPage|FR:FrenchPage|ES:SpanishPage
 *                                  	For other external page: http://server/url
 * @param	string	$target				Target to use on links
 * @param 	int    	$disablejs			More content into html header
 * @param 	int    	$disablehead		More content into html header
 * @param 	array  	$arrayofjs			Array of complementary js files
 * @param 	array  	$arrayofcss			Array of complementary css files
 * @param	string	$morequerystring	Query string to add to the link "print" to get same parameters (use only if autodetect fails)
 * @param   string  $morecssonbody      More CSS on body tag.
 * @param	string	$replacemainareaby	Replace call to main_area() by a print of this string
 * @return	void
 */
function digiriskHeader($head = '', $title = '', $help_url = '', $target = '', $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '', $morequerystring = '', $morecssonbody = '', $replacemainareaby = '')
{
	global $conf, $langs, $db;

	dol_include_once('/digiriskdolibarr/class/digiriskelement.class.php');
	dol_include_once('/custom/digiriskdolibarr/core/modules/digiriskdolibarr/digiriskelement/groupment/mod_groupment_standard.php');
	dol_include_once('/custom/digiriskdolibarr/core/modules/digiriskdolibarr/digiriskelement/workunit/mod_workunit_standard.php');

	$mod_groupment = new $conf->global->DIGIRISKDOLIBARR_GROUPMENT_ADDON();
	$mod_workunit = new $conf->global->DIGIRISKDOLIBARR_WORKUNIT_ADDON();

	// html header
	$tmpcsstouse = 'sidebar-collapse'.($morecssonbody ? ' '.$morecssonbody : '');
	// If theme MD and classic layer, we open the menulayer by default.
	if ($conf->theme == 'md' && !in_array($conf->browser->layout, array('phone', 'tablet')) && empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
	{
		global $mainmenu;
		if ($mainmenu != 'website') $tmpcsstouse = $morecssonbody; // We do not use sidebar-collpase by default to have menuhider open by default.
	}

	if (!empty($conf->global->MAIN_OPTIMIZEFORCOLORBLIND)) {
		$tmpcsstouse .= ' colorblind-'.strip_tags($conf->global->MAIN_OPTIMIZEFORCOLORBLIND);
	}

	print '<body id="mainbody" class="'.$tmpcsstouse.'">'."\n";

	llxHeader('', $title, $help_url);

	//Body navigation digirisk
	$object  = new DigiriskElement($db);
	$objects = $object->fetchAll('', '', 0,0,array('entity' => $conf->entity));
	$results = recurse_tree(0,0,$objects); ?>
	<div id="id-container" class="id-container page-ut-gp-list">
		<div class="side-nav">
			<div id="id-left">
				<div class="digirisk-wrap wpeo-wrap">
					<div class="navigation-container">
						<div class="society-header">
							<a class="linkElement" href="../digiriskdolibarr/digiriskstandard_card.php?id=<?php echo $conf->global->DIGIRISKDOLIBARR_ACTIVE_STANDARD ?>">
								<span class="icon fas fa-building fa-fw"></span>
								<div class="title"><?php echo $conf->global->MAIN_INFO_SOCIETE_NOM ?></div>
								<div class="add-container">
									<a id="newGroupment" href="../digiriskdolibarr/digiriskelement_card.php?action=create&element_type=groupment&fk_parent=0">
										<div class="wpeo-button button-square-40 button-secondary wpeo-tooltip-event" data-direction="bottom" data-color="light" aria-label="<?php echo $langs->trans('NewGroupment'); ?>"><strong><?php echo $mod_groupment->prefix; ?></strong><span class="button-add animated fas fa-plus-circle"></span></div>
									</a>
									<a id="newWorkunit" href="../digiriskdolibarr/digiriskelement_card.php?action=create&element_type=workunit&fk_parent=0">
										<div class="wpeo-button button-square-40 wpeo-tooltip-event" data-direction="bottom" data-color="light" aria-label="<?php echo $langs->trans('NewWorkUnit'); ?>"><strong><?php echo $mod_workunit->prefix; ?></strong><span class="button-add animated fas fa-plus-circle"></span></div>
									</a>
								</div>
							</a>
						</div>
						<div class="toolbar">
							<div class="toggle-plus tooltip hover" aria-label="<?php echo $langs->trans('UnwrapAll'); ?>"><span class="icon fas fa-plus-square"></span></div>
							<div class="toggle-minus tooltip hover" aria-label="<?php echo $langs->trans('WrapAll'); ?>"><span class="icon fas fa-minus-square"></span></div>
						</div>
						<ul class="workunit-list">
								<?php display_recurse_tree($results) ?>
								<script>
								// Get previous menu to display it
								var MENU = localStorage.menu;
								if (MENU == null || MENU == '') {
									MENU = new Set()
								} else {
									MENU = JSON.parse(MENU);
									MENU = new Set(MENU);
								}

								MENU.forEach((id) =>  {
									jQuery( '#menu'+id).removeClass( 'fa-chevron-right').addClass( 'fa-chevron-down' );
									jQuery( '#unit'+id ).addClass( 'toggled' );
								});

								// Set active unit active
								jQuery( '.digirisk-wrap .navigation-container .unit.active' ).removeClass( 'active' );

								var params = new window.URLSearchParams(window.location.search);
								var id = params.get('id');
								if (document.URL.match(/digiriskelement/)) {
									jQuery( '#unit'  + id ).addClass( 'active' );
									jQuery( '#unit'  +id  ).closest( '.unit' ).attr( 'value', id );
                                };
								</script>
							</ul>
					</div>
				</div>
			</div>
		</div>
	<?php

	// main area
	if ($replacemainareaby)
	{
		print $replacemainareaby;
		return;
	}
	main_area($title);
}

/**
 *	Recursive tree process
 *
 * @param	DigiriskElement $parent Element Parent of Digirisk Element object
 * @param 	int             $niveau Depth of tree
 * @param 	array           $array  Global Digirisk Element list
 * @return	array           $result Global Digirisk Element list after recursive process
 */
function recurse_tree($parent, $niveau, $array) {
	$result = array();
	foreach ($array as $noeud) {
		if ($parent == $noeud->fk_parent) {
			$result[$noeud->id] = array(
				'id'       => $noeud->id,
				'object'   => $noeud,
				'children' => recurse_tree($noeud->id, ($niveau + 1), $array),
			);
		}
	}
	return $result;
}

/**
 *	Display Recursive tree process
 *
 * @param	array $result Global Digirisk Element list after recursive process
 * @return	void
 */
function display_recurse_tree($results) {
	global $conf, $langs;

	dol_include_once('/custom/digiriskdolibarr/core/modules/digiriskdolibarr/digiriskelement/groupment/mod_groupment_standard.php');
	dol_include_once('/custom/digiriskdolibarr/core/modules/digiriskdolibarr/digiriskelement/workunit/mod_workunit_standard.php');

	$mod_groupment = new $conf->global->DIGIRISKDOLIBARR_GROUPMENT_ADDON();
	$mod_workunit = new $conf->global->DIGIRISKDOLIBARR_WORKUNIT_ADDON();

	if ( !empty( $results ) ) {
		foreach ($results as $element) { ?>
			<li class="unit type-<?php echo $element['object']->element_type; ?>" id="unit<?php  echo $element['object']->id; ?>">
				<div class="unit-container">
					<?php if ($element['object']->element_type == 'groupment' && count($element['children'])) { ?>
						<div class="toggle-unit">
							<i class="toggle-icon fas fa-chevron-right" id="menu<?php echo $element['object']->id;?>"></i>
						</div>
					<?php } else { ?>
						<div class="spacer"></div>
					<?php } ?>
					<?php $filearray = dol_dir_list($conf->digiriskdolibarr->multidir_output[$conf->entity].'/'.$element['object']->element_type.'/'.$element['object']->ref.'/', "files", 0, '', '(\.odt|_preview.*\.png)$', 'position_name', 'asc', 1);
					if (count($filearray)) {
						print '<span class="floatleft inline-block valignmiddle divphotoref">'.digirisk_show_photos('digiriskdolibarr', $conf->digiriskdolibarr->multidir_output[$conf->entity].'/'.$element['object']->element_type, 'small', 1, 0, 0, 0, 50, 0, 0, 0, 0, $element['object']->element_type, $element['object']).'</span>';
					} else {
						$nophoto = '/public/theme/common/nophoto.png'; ?>
						<span class="floatleft inline-block valignmiddle divphotoref"><img class="photodigiriskdolibarr" alt="No photo" src="<?php echo DOL_URL_ROOT.$nophoto ?>"></span>
					<?php } ?>
					<div class="title" id="scores" value="<?php echo $element['object']->id ?>" >
							<a id="slider" class="linkElement id<?php echo $element['object']->id;?>" href="../digiriskdolibarr/digiriskelement_risk.php?id=<?php echo $element['object']->id; ?>">
								<span class="title-container">
									<span class="ref"><?php echo $element['object']->ref; ?></span>
									<span class="name"><?php echo $element['object']->label; ?></span>
								</span>
							</a>
					</div>
					<?php if ($element['object']->element_type == 'groupment') { ?>
						<div class="add-container">
							<a id="newGroupment" href="../digiriskdolibarr/digiriskelement_card.php?action=create&element_type=groupment&fk_parent=<?php echo $element['object']->id; ?>">
								<div
									class="wpeo-button button-secondary button-square-40 wpeo-tooltip-event"
									data-direction="bottom" data-color="light"
									aria-label="<?php echo $langs->trans('NewGroupment'); ?>">
									<strong><?php echo $mod_groupment->prefix; ?></strong>
									<span class="button-add animated fas fa-plus-circle"></span>
								</div>
							</a>
							<a id="newWorkunit" href="../digiriskdolibarr/digiriskelement_card.php?action=create&element_type=workunit&fk_parent=<?php echo $element['object']->id; ?>">
								<div
									class="wpeo-button button-square-40 wpeo-tooltip-event"
									data-direction="bottom" data-color="light"
									aria-label="<?php echo $langs->trans('NewWorkUnit'); ?>">
									<strong><?php echo $mod_workunit->prefix; ?></strong>
									<span class="button-add animated fas fa-plus-circle"></span>
								</div>
							</a>
						</div>
					<?php } ?>
				</div>
				<ul class="sub-list"><?php display_recurse_tree($element['children']) ?></ul>
			</li>
		<?php }
	}
}

/**
 *  Show tab footer of a card.
 *  Note: $object->next_prev_filter can be set to restrict select to find next or previous record by $form->showrefnav.
 *
 *  @param	Object	$object			Object to show
 *  @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
 *  @param	string	$morehtml  		More html content to output just before the nav bar
 *  @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
 *  @param	string	$fieldid   		Nom du champ en base a utiliser pour select next et previous (we make the select max and min on this field). Use 'none' for no prev/next search.
 *  @param	string	$fieldref   	Nom du champ objet ref (object->ref) a utiliser pour select next et previous
 *  @param	string	$morehtmlref  	More html to show after ref
 *  @param	string	$moreparam  	More param to add in nav link url.
 *	@param	int		$nodbprefix		Do not include DB prefix to forge table name
 *	@param	string	$morehtmlleft	More html code to show before ref
 *	@param	string	$morehtmlstatus	More html code to show under navigation arrows
 *  @param  int     $onlybanner     Put this to 1, if the card will contains only a banner (this add css 'arearefnobottom' on div)
 *	@param	string	$morehtmlright	More html code to show before navigation arrows
 *  @return	void
 */
function digirisk_banner_tab($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $onlybanner = 0, $morehtmlright = '')
{
	global $form;

	print '<div class="'.($onlybanner ? 'arearefnobottom ' : 'arearef ').'heightref valignmiddle centpercent">';
	print $form->showrefnav($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, $morehtmlref, $moreparam, $nodbprefix, $morehtmlleft, $morehtmlstatus, $morehtmlright);
	print '</div>';
	print '<div class="underrefbanner clearboth"></div>';
}

/**
 * Get and save an upload file (for example after submitting a new file a mail form). Database index of file is also updated if donotupdatesession is set.
 * All information used are in db, conf, langs, user and _FILES.
 * Note: This function can be used only into a HTML page context.
 *
 * @param	string	$upload_dir				Directory where to store uploaded file (note: used to forge $destpath = $upload_dir + filename)
 * @param	int		$allowoverwrite			1=Allow overwrite existing file
 * @param	int		$donotupdatesession		1=Do no edit _SESSION variable but update database index. 0=Update _SESSION and not database index. -1=Do not update SESSION neither db.
 * @param	string	$varfiles				_FILES var name
 * @param	string	$savingdocmask			Mask to use to define output filename. For example 'XXXXX-__YYYYMMDD__-__file__'
 * @param	string	$link					Link to add (to add a link instead of a file)
 * @param   string  $trackid                Track id (used to prefix name of session vars to avoid conflict)
 * @param	int		$generatethumbs			1=Generate also thumbs for uploaded image files
 * @return	int                             <=0 if KO, >0 if OK
 */
function digirisk_add_file_process($upload_dir, $allowoverwrite = 0, $donotupdatesession = 0, $varfiles = 'addedfile', $savingdocmask = '', $link = null, $trackid = '', $generatethumbs = 1, $files)
{
	global $db, $user, $conf, $langs;

	$res = 0;

	if (!empty($_FILES[$varfiles])) // For view $_FILES[$varfiles]['error']
	{
		dol_syslog('dol_add_file_process upload_dir='.$upload_dir.' allowoverwrite='.$allowoverwrite.' donotupdatesession='.$donotupdatesession.' savingdocmask='.$savingdocmask, LOG_DEBUG);

		if (dol_mkdir($upload_dir) >= 0)
		{
			$TFile = $_FILES[$varfiles];
			if (!is_array($TFile['name']))
			{
				foreach ($TFile as $key => &$val)
				{
					$val = array($val);
				}
			}

			$nbfile = count($TFile['name']);
			$nbok = 0;
			for ($i = 0; $i < $nbfile; $i++)
			{
				// Define $destfull (path to file including filename) and $destfile (only filename)
				$destfull = $upload_dir."/".$TFile['name'][$i];
				$destfile = $TFile['name'][$i];
				$destfilewithoutext = preg_replace('/\.[^\.]+$/', '', $destfile);

				if ($savingdocmask && strpos($savingdocmask, $destfilewithoutext) !== 0)
				{
					$destfull = $upload_dir."/".preg_replace('/__file__/', $TFile['name'][$i], $savingdocmask);
					$destfile = preg_replace('/__file__/', $TFile['name'][$i], $savingdocmask);
				}

				$filenameto = basename($destfile);
				if (preg_match('/^\./', $filenameto)) {
					$langs->load("errors"); // key must be loaded because we can't rely on loading during output, we need var substitution to be done now.
					setEventMessages($langs->trans("ErrorFilenameCantStartWithDot", $filenameto), null, 'errors');
					break;
				}

				// dol_sanitizeFileName the file name and lowercase extension
				$info = pathinfo($destfull);
				$destfull = $info['dirname'].'/'.dol_sanitizeFileName($info['filename'].($info['extension'] != '' ? ('.'.strtolower($info['extension'])) : ''));
				$info = pathinfo($destfile);

				$destfile = dol_sanitizeFileName($info['filename'].($info['extension'] != '' ? ('.'.strtolower($info['extension'])) : ''));

				// We apply dol_string_nohtmltag also to clean file names (this remove duplicate spaces) because
				// this function is also applied when we rename and when we make try to download file (by the GETPOST(filename, 'alphanohtml') call).
				$destfile = dol_string_nohtmltag($destfile);
				$destfull = dol_string_nohtmltag($destfull);

				// Move file from temp directory to final directory. A .noexe may also be appended on file name.
				$resupload = dol_move_uploaded_file($TFile['tmp_name'][$i], $destfull, $allowoverwrite, 0, $TFile['error'][$i], 0, $varfiles, $upload_dir);

				if (is_numeric($resupload) && $resupload > 0)   // $resupload can be 'ErrorFileAlreadyExists'
				{
					global $maxwidthsmall, $maxheightsmall, $maxwidthmini, $maxheightmini;

					include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

					// Generate thumbs.
					if ($generatethumbs)
					{
						if (image_format_supported($destfull) == 1)
						{
							// Create thumbs
							// We can't use $object->addThumbs here because there is no $object known

							// Used on logon for example
							$imgThumbSmall = vignette($destfull, $maxwidthsmall, $maxheightsmall, '_small', 50, "thumbs");
							// Create mini thumbs for image (Ratio is near 16/9)
							// Used on menu or for setup page for example
							$imgThumbMini = vignette($destfull, $maxwidthmini, $maxheightmini, '_mini', 50, "thumbs");
						}
					}

					// Update session
					if (empty($donotupdatesession))
					{
						include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
						$formmail = new FormMail($db);
						$formmail->trackid = $trackid;
						$formmail->add_attached_files($destfull, $destfile, $TFile['type'][$i]);
					}

					// Update index table of files (llx_ecm_files)
					if ($donotupdatesession == 1)
					{
						$result = addFileIntoDatabaseIndex($upload_dir, basename($destfile).($resupload == 2 ? '.noexe' : ''), $TFile['name'][$i], 'uploaded', 0);
						if ($result < 0)
						{
							if ($allowoverwrite) {
								// Do not show error message. We can have an error due to DB_ERROR_RECORD_ALREADY_EXISTS
							}
							else {
								setEventMessages('WarningFailedToAddFileIntoDatabaseIndex', '', 'warnings');
							}
						}
					}

					$nbok++;
				}
				else
				{
					$langs->load("errors");
					if ($resupload < 0)	// Unknown error
					{
						setEventMessages($langs->trans("ErrorFileNotUploaded"), null, 'errors');
					}
					elseif (preg_match('/ErrorFileIsInfectedWithAVirus/', $resupload))	// Files infected by a virus
					{
						setEventMessages($langs->trans("ErrorFileIsInfectedWithAVirus"), null, 'errors');
					}
					else	// Known error
					{
						setEventMessages($langs->trans($resupload), null, 'errors');
					}
				}
			}
			if ($nbok > 0)
			{
				$res = 1;
				setEventMessages($langs->trans("FileTransferComplete"), null, 'mesgs');
			}
		}
	} elseif ($link) {
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$linkObject = new Link($db);
		$linkObject->entity = $conf->entity;
		$linkObject->url = $link;
		$linkObject->objecttype = GETPOST('objecttype', 'alpha');
		$linkObject->objectid = GETPOST('objectid', 'int');
		$linkObject->label = GETPOST('label', 'alpha');
		$res = $linkObject->create($user);
		$langs->load('link');
		if ($res > 0) {
			setEventMessages($langs->trans("LinkComplete"), null, 'mesgs');
		} else {
			setEventMessages($langs->trans("ErrorFileNotLinked"), null, 'errors');
		}
	}
	else
	{
		$langs->load("errors");
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("File")), null, 'errors');
	}

	return $res;
}
