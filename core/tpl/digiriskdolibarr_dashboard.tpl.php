<?php
/*
 *  Actions
*/

if ($action == 'adddashboardinfo') {
	$data = json_decode(file_get_contents('php://input'), true);

	$dashboardWidgetName = $data['widgetName'];

	$visible = json_decode($user->conf->DIGIRISKDOLIBARR_DISABLED_DASHBOARD_INFO);
	unset($visible->$dashboardWidgetName);

	$tabparam['DIGIRISKDOLIBARR_DISABLED_DASHBOARD_INFO'] = json_encode($visible);

	dol_set_user_param($db, $conf, $user, $tabparam);
	$action = '';
}

if ($action == 'closedashboardinfo') {
	$data = json_decode(file_get_contents('php://input'), true);

	$dashboardWidgetName = $data['dashboardWidgetName'];

	$visible = json_decode($user->conf->DIGIRISKDOLIBARR_DISABLED_DASHBOARD_INFO);
	$visible->$dashboardWidgetName = 0;

	$tabparam['DIGIRISKDOLIBARR_DISABLED_DASHBOARD_INFO'] = json_encode($visible);

	dol_set_user_param($db, $conf, $user, $tabparam);
	$action = '';
}

/*
 * View
 */

$dataseries = array(
	'risk' => $stats->load_dashboard_risk(),
	'task' => $stats->load_dashboard_task()
);

$accidentdata = $stats->load_dashboard_accident();
$riskassementdocumentdata = $stats->load_dashboard_riskassementdocument();

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '" class="dashboard" id="dashBoardForm">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="view">';

$dashboardLines = array(
	'daywithoutaccident' => array(
		'label' => $langs->trans("DayWithoutAccident"),
		'content' => $accidentdata,
		'picto' => 'fas fa-user-injured'
	),
	'lastgenerationdateDU' => array(
		'label' => $langs->trans("LastGenerateDate"),
		'content' => $riskassementdocumentdata[0],
		'picto' => 'fas fa-info-circle'
	),
	'nextgenerationdateDU' => array(
		'label' => $langs->trans("NextGenerateDate"),
		'content' => $riskassementdocumentdata[1],
		'picto' => 'fas fa-info-circle'
	),
);

$disableWidgetList = json_decode($user->conf->DIGIRISKDOLIBARR_DISABLED_DASHBOARD_INFO);

print '<div class="add-widget-box" style="' . (!empty($disableWidgetList) ? '' : 'display:none') . '">';
print Form::selectarray('boxcombo', $dashboardLines, -1, $langs->trans("ChooseBoxToAdd") . '...', 0, 0, '', 0, 0, 0, 'ASC', 'maxwidth150onsmartphone hideonprint add-dashboard-widget', 0, 'hidden selected', 0, 1);
if (!empty($conf->use_javascript_ajax)) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	print ajax_combobox("boxcombo");
}
print '</div>';
print '<div class="fichecenter">';

if (!empty($dashboardLines)) {
	$openedDashBoard = '';
	foreach ($dashboardLines as $key => $dashboardLine) {
		if (!isset($disableWidgetList->$key)) {
			$openedDashBoard .= '<div class="box-flex-item"><div class="box-flex-item-with-margin">';
			$openedDashBoard .= '<div class="info-box info-box-sm">';
			$openedDashBoard .= '<span class="info-box-icon">';
			$openedDashBoard .= '<i class="' . $dashboardLine["picto"] . '"></i>';
			$openedDashBoard .= '</span>';
			$openedDashBoard .= '<div class="info-box-content">';
			$openedDashBoard .= '<div class="info-box-title" title="' . $langs->trans("Close") . '">';
			$openedDashBoard .= '<span class="close-dashboard-info" data-widgetname="' . $key . '"><i class="fas fa-times"></i></span>';
			$openedDashBoard .= '</div>';
			$openedDashBoard .= '<div class="info-box-lines">';
			$openedDashBoard .= '<div class="info-box-line" style="font-size : 20px;">';
			$openedDashBoard .= '<span class=""><strong>' . $dashboardLine["label"] . ' ' . '</strong>';
			$openedDashBoard .= '<span class="classfortooltip badge badge-info" title="' . $dashboardLine["label"] . ' ' . $dashboardLine["content"] . '" >' . $dashboardLine["content"] . '</span>';
			$openedDashBoard .= '</span>';
			$openedDashBoard .= '</div>';
			$openedDashBoard .= '</div><!-- /.info-box-lines --></div><!-- /.info-box-content -->';
			$openedDashBoard .= '</div><!-- /.info-box -->';
			$openedDashBoard .= '</div><!-- /.box-flex-item-with-margin -->';
			$openedDashBoard .= '</div>';
		}
	}
	print '<div class="opened-dash-board-wrap"><div class="box-flex-container">' . $openedDashBoard . '</div></div>';
}

print '<div class="box-flex-container">';

if (is_array($dataseries) && !empty($dataseries)) {
	foreach ($dataseries as $keyelement => $datagraph['data']) {
		if (is_array($datagraph['data']) && !empty($datagraph['data'])) {
			$arraykeys = array_keys($datagraph['data']['data']);
			foreach ($arraykeys as $key) {
				$data[$keyelement][] = array(
					0 => $langs->trans($datagraph['data']['labels'][$key]['label']),
					1 => $datagraph['data']['data'][$key]
				);
				$datacolor[$keyelement][] = $langs->trans($datagraph['data']['labels'][$key]['color']);
			}

			$filename[$keyelement] = $keyelement . '.png';
			$fileurl[$keyelement]  = DOL_URL_ROOT . '/viewimage.php?modulepart=digiriskdolibarr&file=' . $keyelement . '.png';

			$graph = new DolGraph();
			$graph->SetData($data[$keyelement]);
			$graph->SetDataColor($datacolor[$keyelement]);
			$graph->SetType(array('pie'));
			$graph->SetWidth($WIDTH);
			$graph->SetHeight($HEIGHT);
			$graph->setShowLegend(2);
			$graph->draw($filename[$keyelement], $fileurl[$keyelement]);
			print '<div class="box-flex-item">';
			print '<div class="titre inline-block">';
			print $datagraph['data']['picto'] . ' ' . $datagraph['data']['title'];
			print '</div>';
			print $graph->show();
			print '</div>';
		}
	}
}

print '</div></div></div>';
print '</form>';
