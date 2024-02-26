<?php
/* Copyright (C) 2021-2024 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/digiriskdocuments/legaldisplay.class.php
 * \ingroup     digiriskdolibarr
 * \brief       This file is a class file for LegalDisplay
 */

// Load DigiriskDolibarr libraries
require_once __DIR__ . '/../digiriskdocuments.class.php';
require_once __DIR__ . '/../digiriskresources.class.php';

/**
 * Class for LegalDisplay
 */
class LegalDisplay extends DigiriskDocuments
{
	/**
	 * @var string Module name
	 */
	public $module = 'digiriskdolibarr';

	/**
	 * @var string Element type of object
	 */
	public $element = 'legaldisplay';

    /**
     * @var string String with name of icon for legaldisplay. Must be the part after the 'object_' into object_legaldisplay.png
     */
    public $picto = 'fontawesome_fa-file_fas_#d35968';

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		parent::__construct($db, $this->module, $this->element);
	}

	/**
	 * Function for JSON filling before saving in database for documents
	 *
	 * @return false|string
	 * @throws Exception
	 */
	public function LegalDisplayFillJSON() {
		global $conf, $langs;

		$resources               = new DigiriskResources($this->db);
		$digirisk_resources      = $resources->fetchDigiriskResources();
		$json                    = array();

		// *** JSON FILLING ***
		if (!empty ($digirisk_resources )) {
			$labour_doctor_societe = new Societe($this->db);
			$result = $labour_doctor_societe->fetch($digirisk_resources['LabourDoctorSociety']->id[0]);
			if ($result > 0) {
				$morewhere = ' AND element_id = ' . $labour_doctor_societe->id;
				$morewhere .= ' AND element_type = ' . "'" . $labour_doctor_societe->element . "'";
				$morewhere .= ' AND status = 1';

				$thirdparty_openinghours = new SaturneSchedules($this->db);
				$thirdparty_openinghours->fetch(0, '', $morewhere);
				$json['LegalDisplay']['occupational_health_service']['openinghours']                       = $langs->trans('Monday') . ' : ' . $thirdparty_openinghours->monday . "\r\n" . $langs->trans('Tuesday') . ' : ' . $thirdparty_openinghours->tuesday . "\r\n" . $langs->trans('Wednesday') . ' : ' . $thirdparty_openinghours->wednesday . "\r\n" . $langs->trans('Thursday') . ' : ' . $thirdparty_openinghours->thursday . "\r\n" . $langs->trans('Friday') . ' : ' . $thirdparty_openinghours->friday . "\r\n" . $langs->trans('Saturday') . ' : ' . $thirdparty_openinghours->saturday . "\r\n" . $langs->trans('Sunday') . ' : ' . $thirdparty_openinghours->sunday;
                $json['LegalDisplay']['occupational_health_service']['opening_hours_details']['monday']    = $thirdparty_openinghours->monday;
                $json['LegalDisplay']['occupational_health_service']['opening_hours_details']['tuesday']   = $thirdparty_openinghours->tuesday;
                $json['LegalDisplay']['occupational_health_service']['opening_hours_details']['wednesday'] = $thirdparty_openinghours->wednesday;
                $json['LegalDisplay']['occupational_health_service']['opening_hours_details']['thursday']  = $thirdparty_openinghours->thursday;
                $json['LegalDisplay']['occupational_health_service']['opening_hours_details']['friday']    = $thirdparty_openinghours->friday;
                $json['LegalDisplay']['occupational_health_service']['opening_hours_details']['saturday']  = $thirdparty_openinghours->saturday;
                $json['LegalDisplay']['occupational_health_service']['opening_hours_details']['sunday']    = $thirdparty_openinghours->sunday;
			}

			$labour_doctor_contact = new Contact($this->db);
			$result = $labour_doctor_contact->fetch($digirisk_resources['LabourDoctorContact']->id[0]);
			if ($result > 0) {
				$json['LegalDisplay']['occupational_health_service']['id']       = $labour_doctor_contact->id;
				$json['LegalDisplay']['occupational_health_service']['name']     = $labour_doctor_contact->firstname . " " . $labour_doctor_contact->lastname;
				$json['LegalDisplay']['occupational_health_service']['address']  = preg_replace('/\s\s+/', ' ', $labour_doctor_contact->address);
				$json['LegalDisplay']['occupational_health_service']['zip']      = $labour_doctor_contact->zip;
				$json['LegalDisplay']['occupational_health_service']['town']     = $labour_doctor_contact->town;
				$json['LegalDisplay']['occupational_health_service']['phone']    = $labour_doctor_contact->phone_pro;
                $json['LegalDisplay']['occupational_health_service']['fullname'] = $labour_doctor_contact->getNomUrl(1);
			}

			$labour_inspector_societe = new Societe($this->db);
			$result = $labour_inspector_societe->fetch($digirisk_resources['LabourInspectorSociety']->id[0]);

			if ($result > 0) {
				$morewhere = ' AND element_id = ' . $labour_inspector_societe->id;
				$morewhere .= ' AND element_type = ' . "'" . $labour_inspector_societe->element . "'";
				$morewhere .= ' AND status = 1';
				$thirdparty_openinghours = new SaturneSchedules($this->db);
				$thirdparty_openinghours->fetch(0, '', $morewhere);

				$json['LegalDisplay']['detective_work']['openinghours']                       = $langs->trans('Monday') . ' : ' . $thirdparty_openinghours->monday . "\r\n" . $langs->trans('Tuesday') . ' : ' . $thirdparty_openinghours->tuesday . "\r\n" . $langs->trans('Wednesday') . ' : ' . $thirdparty_openinghours->wednesday . "\r\n" . $langs->trans('Thursday') . ' : ' . $thirdparty_openinghours->thursday . "\r\n" . $langs->trans('Friday') . ' : ' . $thirdparty_openinghours->friday . "\r\n" . $langs->trans('Saturday') . ' : ' . $thirdparty_openinghours->saturday . "\r\n" . $langs->trans('Sunday') . ' : ' . $thirdparty_openinghours->sunday;
                $json['LegalDisplay']['detective_work']['opening_hours_details']['monday']    = $thirdparty_openinghours->monday;
                $json['LegalDisplay']['detective_work']['opening_hours_details']['tuesday']   = $thirdparty_openinghours->tuesday;
                $json['LegalDisplay']['detective_work']['opening_hours_details']['wednesday'] = $thirdparty_openinghours->wednesday;
                $json['LegalDisplay']['detective_work']['opening_hours_details']['thursday']  = $thirdparty_openinghours->thursday;
                $json['LegalDisplay']['detective_work']['opening_hours_details']['friday']    = $thirdparty_openinghours->friday;
                $json['LegalDisplay']['detective_work']['opening_hours_details']['saturday']  = $thirdparty_openinghours->saturday;
                $json['LegalDisplay']['detective_work']['opening_hours_details']['sunday']    = $thirdparty_openinghours->sunday;
			}

			$labourInspectorContact = new Contact($this->db);
			$result = $labourInspectorContact->fetch($digirisk_resources['LabourInspectorContact']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['detective_work']['id']       = $labourInspectorContact->id;
				$json['LegalDisplay']['detective_work']['name']     = $labourInspectorContact->firstname . " " . $labourInspectorContact->lastname;
				$json['LegalDisplay']['detective_work']['address']  = preg_replace('/\s\s+/', ' ', $labourInspectorContact->address);
				$json['LegalDisplay']['detective_work']['zip']      = $labourInspectorContact->zip;
				$json['LegalDisplay']['detective_work']['town']     = $labourInspectorContact->town;
				$json['LegalDisplay']['detective_work']['phone']    = $labourInspectorContact->phone_pro;
                $json['LegalDisplay']['detective_work']['fullname'] = $labourInspectorContact->getNomUrl(1);
			}

			$samu = new Societe($this->db);
			$result = $samu->fetch($digirisk_resources['SAMU']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['emergency_service']['samu'] = $samu->phone;
			}

			$police = new Societe($this->db);
			$result = $police->fetch($digirisk_resources['Police']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['emergency_service']['police'] = $police->phone;
			}

			$pompier = new Societe($this->db);
			$result = $pompier->fetch($digirisk_resources['Pompiers']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['emergency_service']['pompier'] = $pompier->phone;
			}

			$emergency = new Societe($this->db);
			$result = $emergency->fetch($digirisk_resources['AllEmergencies']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['emergency_service']['emergency'] = $emergency->phone;
			}

			$rights_defender = new Societe($this->db);
			$result = $rights_defender->fetch($digirisk_resources['RightsDefender']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['emergency_service']['right_defender'] = $rights_defender->phone;
			}

			$poison_control_center = new Societe($this->db);
			$result = $poison_control_center->fetch($digirisk_resources['PoisonControlCenter']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['emergency_service']['poison_control_center'] = $poison_control_center->phone;
			}

			$responsible_prevent = new User($this->db);
			$result = $responsible_prevent->fetch($digirisk_resources['Responsible']->id[0]);

			if ($result > 0) {
				$json['LegalDisplay']['safety_rule']['id']                         = $responsible_prevent->id;
				$json['LegalDisplay']['safety_rule']['responsible_for_preventing'] = $responsible_prevent->firstname . " " . $responsible_prevent->lastname;
				$json['LegalDisplay']['safety_rule']['phone']                      = $responsible_prevent->office_phone;
			}

			$opening_hours_monday    = explode(' ', $conf->global->MAIN_INFO_OPENINGHOURS_MONDAY);
			$opening_hours_tuesday   = explode(' ', $conf->global->MAIN_INFO_OPENINGHOURS_TUESDAY);
			$opening_hours_wednesday = explode(' ', $conf->global->MAIN_INFO_OPENINGHOURS_WEDNESDAY);
			$opening_hours_thursday  = explode(' ', $conf->global->MAIN_INFO_OPENINGHOURS_THURSDAY);
			$opening_hours_friday    = explode(' ', $conf->global->MAIN_INFO_OPENINGHOURS_FRIDAY);
			$opening_hours_saturday  = explode(' ', $conf->global->MAIN_INFO_OPENINGHOURS_SATURDAY);
			$opening_hours_sunday    = explode(' ', $conf->global->MAIN_INFO_OPENINGHOURS_SUNDAY);

			$json['LegalDisplay']['working_hour']['monday_morning']    = $opening_hours_monday[0];
			$json['LegalDisplay']['working_hour']['tuesday_morning']   = $opening_hours_tuesday[0];
			$json['LegalDisplay']['working_hour']['wednesday_morning'] = $opening_hours_wednesday[0];
			$json['LegalDisplay']['working_hour']['thursday_morning']  = $opening_hours_thursday[0];
			$json['LegalDisplay']['working_hour']['friday_morning']    = $opening_hours_friday[0];
			$json['LegalDisplay']['working_hour']['saturday_morning']  = $opening_hours_saturday[0];
			$json['LegalDisplay']['working_hour']['sunday_morning']    = $opening_hours_sunday[0];

			$json['LegalDisplay']['working_hour']['monday_afternoon']    = $opening_hours_monday[1];
			$json['LegalDisplay']['working_hour']['tuesday_afternoon']   = $opening_hours_tuesday[1];
			$json['LegalDisplay']['working_hour']['wednesday_afternoon'] = $opening_hours_wednesday[1];
			$json['LegalDisplay']['working_hour']['thursday_afternoon']  = $opening_hours_thursday[1];
			$json['LegalDisplay']['working_hour']['friday_afternoon']    = $opening_hours_friday[1];
			$json['LegalDisplay']['working_hour']['saturday_afternoon']  = $opening_hours_saturday[1];
			$json['LegalDisplay']['working_hour']['sunday_afternoon']    = $opening_hours_sunday[1];

			$json['LegalDisplay']['safety_rule']['location_of_detailed_instruction']                      = $conf->global->DIGIRISKDOLIBARR_LOCATION_OF_DETAILED_INSTRUCTION;
			$json['LegalDisplay']['derogation_schedule']['permanent']                                     = $conf->global->DIGIRISKDOLIBARR_DEROGATION_SCHEDULE_PERMANENT;
			$json['LegalDisplay']['derogation_schedule']['occasional']                                    = $conf->global->DIGIRISKDOLIBARR_DEROGATION_SCHEDULE_OCCASIONAL;
			$json['LegalDisplay']['collective_agreement']['title_of_the_applicable_collective_agreement'] = $conf->global->DIGIRISKDOLIBARR_COLLECTIVE_AGREEMENT_TITLE;
			$json['LegalDisplay']['collective_agreement']['title_of_the_applicable_collective_agreement'] = $conf->global->DIGIRISKDOLIBARR_COLLECTIVE_AGREEMENT_TITLE . ' - ' . $this->getIDCCByCode($conf->global->DIGIRISKDOLIBARR_COLLECTIVE_AGREEMENT_TITLE)->libelle;
			$json['LegalDisplay']['collective_agreement']['location_and_access_terms_of_the_agreement']   = $conf->global->DIGIRISKDOLIBARR_COLLECTIVE_AGREEMENT_LOCATION;
			$json['LegalDisplay']['DUER']['how_access_to_duer']                                           = $conf->global->DIGIRISKDOLIBARR_DUER_LOCATION;
			$json['LegalDisplay']['rules']['location']                                                    = $conf->global->DIGIRISKDOLIBARR_RULES_LOCATION;
			$json['LegalDisplay']['participation_agreement']['information_procedures']                    = $conf->global->DIGIRISKDOLIBARR_PARTICIPATION_AGREEMENT_INFORMATION_PROCEDURE;

			$jsonFormatted = json_encode($json, JSON_UNESCAPED_UNICODE);

			return $jsonFormatted;
		}
		else
		{
			return -1;
		}
	}

	/**
	 * Get Collective Convention label with code
	 *
	 * @param $code
	 * @return Object|string
	 */
	public function getIDCCByCode($code) {
		if (isset($code) && $code !== '') {
			$sql = "SELECT rowid, libelle";
			$sql .= " FROM ".MAIN_DB_PREFIX.'c_conventions_collectives';
			$sql .= " WHERE code = " . $code ;

			$result = $this->db->query($sql);
			$obj = '';
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
			}
			else {
				dol_print_error($this->db);
			}

			return $obj;
		} else {
			return '';
		}
	}

    /**
     * Load dashboard info
     *
     * @return array     $dashboardData Return all dashboardData after load info
     * @throws Exception
     */
    public function load_dashboard(): array
    {
        global $langs;

        $legalDisplay = json_decode($this->LegalDisplayFillJSON(), false, 512, JSON_UNESCAPED_UNICODE)->LegalDisplay;

        $labourDoctor            = [$legalDisplay->occupational_health_service->fullname, $legalDisplay->occupational_health_service->zip, $legalDisplay->occupational_health_service->address, $legalDisplay->occupational_health_service->town, $legalDisplay->occupational_health_service->phone];
        $labourDoctorTime        = [$legalDisplay->occupational_health_service->opening_hours_details->monday, $legalDisplay->occupational_health_service->opening_hours_details->tuesday, $legalDisplay->occupational_health_service->opening_hours_details->wednesday, $legalDisplay->occupational_health_service->opening_hours_details->thursday, $legalDisplay->occupational_health_service->opening_hours_details->friday, $legalDisplay->occupational_health_service->opening_hours_details->saturday, $legalDisplay->occupational_health_service->opening_hours_details->sunday];
        $detectiveWork          = [$legalDisplay->detective_work->fullname, $legalDisplay->detective_work->zip, $legalDisplay->detective_work->address, $legalDisplay->detective_work->town, $legalDisplay->detective_work->phone];
        $labourDetectiveWorkTime = [$legalDisplay->detective_work->opening_hours_details->monday, $legalDisplay->detective_work->opening_hours_details->tuesday, $legalDisplay->detective_work->opening_hours_details->wednesday, $legalDisplay->detective_work->opening_hours_details->thursday, $legalDisplay->detective_work->opening_hours_details->friday, $legalDisplay->detective_work->opening_hours_details->saturday, $legalDisplay->detective_work->opening_hours_details->sunday];
        $emergencyService        = [$legalDisplay->emergency_service->samu, $legalDisplay->emergency_service->pompier, $legalDisplay->emergency_service->police, $legalDisplay->emergency_service->emergency, $legalDisplay->emergency_service->right_defender, $legalDisplay->emergency_service->poison_control_center];
        $safetyRule              = [$legalDisplay->emergency_service->safety_rule->responsible_for_preventing, $legalDisplay->emergency_service->safety_rule->phone, $legalDisplay->emergency_service->safety_rule->location_of_detailed_instruction];
        $workingHour             = [$legalDisplay->detective_work->opening_hours_details->monday, $legalDisplay->working_hour->monday_afternoon, $legalDisplay->working_hour->tuesday_morning, $legalDisplay->working_hour->tuesday_afternoon, $legalDisplay->working_hour->wednesday_morning, $legalDisplay->working_hour->wednesday_afternoon, $legalDisplay->working_hour->thursday_morning, $legalDisplay->working_hour->thursday_afternoon, $legalDisplay->working_hour->friday_morning, $legalDisplay->working_hour->friday_afternoon, $legalDisplay->working_hour->saturday_morning, $legalDisplay->working_hour->saturday_afternoon, $legalDisplay->working_hour->sunday_morning, $legalDisplay->working_hour->sunday_afternoon];
        $parameters              = [$legalDisplay->derogation_schedule->permanent, $legalDisplay->derogation_schedule->occasional, $legalDisplay->DUER->how_access_to_duer, $legalDisplay->participation_agreement->information_procedures, $legalDisplay->collective_agreement->location_and_access_terms_of_the_agreement];

        require_once __DIR__ . '/../../core/tpl/digiriskdolibarr_legaldisplay_percentages.tpl.php';

        $dashboardData['widgets'] = [
            'labour_doctor' => [
                'label'      => [
                    $langs->transnoentities('Name') ?? '',
                    $langs->transnoentities('Zip') ?? '',
                    $langs->transnoentities('Address') ?? '',
                    $langs->transnoentities('Town') ?? '',
                    $langs->transnoentities('Phone') ?? '',
                ],
                'customContent' => [
                    0 => dol_strlen($legalDisplay->occupational_health_service->fullname) > 0 ? $legalDisplay->occupational_health_service->fullname : $langs->trans('NoData'),
                ],
                'content'    => [
                    1 => dol_strlen($legalDisplay->occupational_health_service->zip)     > 0 ? $legalDisplay->occupational_health_service->zip     : $langs->trans('NoData'),
                    2 => dol_strlen($legalDisplay->occupational_health_service->address) > 0 ? $legalDisplay->occupational_health_service->address : $langs->trans('NoData'),
                    3 => dol_strlen($legalDisplay->occupational_health_service->town)    > 0 ? $legalDisplay->occupational_health_service->town    : $langs->trans('NoData'),
                    4 => dol_strlen($legalDisplay->occupational_health_service->phone)   > 0 ? $legalDisplay->occupational_health_service->phone   : $langs->trans('NoData'),
                ],
                'link'        => '<a href="../../admin/securityconf.php" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'       => 'fas fa-user-md',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageLabourDoctor . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName'  => $langs->transnoentities('Society')
            ],
            'labour_doctor_schedules' => [
                'label'      => [
                    $langs->transnoentities('Monday'),
                    $langs->transnoentities('Tuesday'),
                    $langs->transnoentities('Wednesday'),
                    $langs->transnoentities('Thursday'),
                    $langs->transnoentities('Friday'),
                    $langs->transnoentities('Saturday'),
                    $langs->transnoentities('Sunday'),
                ],
                'content'    => [
                    dol_strlen($legalDisplay->occupational_health_service->opening_hours_details->monday)    > 0 ? $legalDisplay->occupational_health_service->opening_hours_details->monday    : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->occupational_health_service->opening_hours_details->tuesday)   > 0 ? $legalDisplay->occupational_health_service->opening_hours_details->tuesday   : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->occupational_health_service->opening_hours_details->wednesday) > 0 ? $legalDisplay->occupational_health_service->opening_hours_details->wednesday : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->occupational_health_service->opening_hours_details->thursday)  > 0 ? $legalDisplay->occupational_health_service->opening_hours_details->thursday  : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->occupational_health_service->opening_hours_details->friday)    > 0 ? $legalDisplay->occupational_health_service->opening_hours_details->friday    : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->occupational_health_service->opening_hours_details->saturday)  > 0 ? $legalDisplay->occupational_health_service->opening_hours_details->saturday  : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->occupational_health_service->opening_hours_details->sunday)    > 0 ? $legalDisplay->occupational_health_service->opening_hours_details->sunday    : $langs->trans('NoData')

                ],
                'link'       => '<a href="' . dol_buildpath('saturne/view/saturne_schedules.php?id=' . $legalDisplay->occupational_health_service->id . '&element_type=societe&module_name=societe', 1) .'" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'      => 'fas fa-clock',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageLabourDoctorTime . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName' => $langs->transnoentities('Society')
            ],
            'labour_inspector' => [
                'label'      => [
                    $langs->transnoentities('Name') ?? '',
                    $langs->transnoentities('Zip') ?? '',
                    $langs->transnoentities('Address') ?? '',
                    $langs->transnoentities('Town') ?? '',
                    $langs->transnoentities('Phone') ?? '',
                ],
                'customContent' => [
                    0 => dol_strlen($legalDisplay->detective_work->fullname) > 0 ? $legalDisplay->detective_work->fullname : $langs->trans('NoData'),
                ],
                'content'    => [
                    1 => dol_strlen($legalDisplay->detective_work->zip)     > 0 ? $legalDisplay->detective_work->zip     : $langs->trans('NoData'),
                    2 => dol_strlen($legalDisplay->detective_work->address) > 0 ? $legalDisplay->detective_work->address : $langs->trans('NoData'),
                    3 => dol_strlen($legalDisplay->detective_work->town)    > 0 ? $legalDisplay->detective_work->address : $langs->trans('NoData'),
                    4 => dol_strlen($legalDisplay->detective_work->phone)   > 0 ? $legalDisplay->detective_work->phone   : $langs->trans('NoData'),
                ],
                'link'       => '<a href="../../admin/securityconf.php" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'      => 'fas fa-briefcase',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageDetectiveWork . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName' => $langs->transnoentities('Society')
            ],
            'detective_work_schedules' => [
                'label'      => [
                    $langs->transnoentities('Monday'),
                    $langs->transnoentities('Tuesday'),
                    $langs->transnoentities('Wednesday'),
                    $langs->transnoentities('Thursday'),
                    $langs->transnoentities('Friday'),
                    $langs->transnoentities('Saturday'),
                    $langs->transnoentities('Sunday'),
                ],
                'content'    => [
                    dol_strlen($legalDisplay->detective_work->opening_hours_details->monday)    > 0 ? $legalDisplay->detective_work->opening_hours_details->monday    : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->detective_work->opening_hours_details->tuesday)   > 0 ? $legalDisplay->detective_work->opening_hours_details->tuesday   : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->detective_work->opening_hours_details->wednesday) > 0 ? $legalDisplay->detective_work->opening_hours_details->wednesday : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->detective_work->opening_hours_details->thursday)  > 0 ? $legalDisplay->detective_work->opening_hours_details->thursday  : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->detective_work->opening_hours_details->friday)    > 0 ? $legalDisplay->detective_work->opening_hours_details->friday    : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->detective_work->opening_hours_details->saturday)  > 0 ? $legalDisplay->detective_work->opening_hours_details->saturday  : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->detective_work->opening_hours_details->sunday)    > 0 ? $legalDisplay->detective_work->opening_hours_details->sunday    : $langs->trans('NoData')

                ],
                'link'       => '<a href="' . dol_buildpath('saturne/view/saturne_schedules.php?id=' . $legalDisplay->detective_work->id . '&element_type=societe&module_name=societe', 1) .'"" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'      => 'fas fa-clock',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageDetectiveWorkTime . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName' => $langs->transnoentities('Society')
            ],
            'emergency_calls' => [
                'label'      => [
                    $langs->transnoentities('SAMU') ?? '',
                    $langs->transnoentities('Pompiers') ?? '',
                    $langs->transnoentities('Police') ?? '',
                    $langs->transnoentities('AllEmergencies') ?? '',
                    $langs->transnoentities('RightsDefender') ?? '',
                    $langs->transnoentities('PoisonControlCenter') ?? '',
                ],
                'content'    => [
                    dol_strlen($legalDisplay->emergency_service->samu)                  > 0 ? $legalDisplay->emergency_service->samu                  : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->emergency_service->pompier)               > 0 ? $legalDisplay->emergency_service->pompier               : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->emergency_service->police)                > 0 ? $legalDisplay->emergency_service->police                : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->emergency_service->emergency)             > 0 ? $legalDisplay->emergency_service->emergency             : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->emergency_service->right_defender)        > 0 ? $legalDisplay->emergency_service->right_defender        : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->emergency_service->poison_control_center) > 0 ? $legalDisplay->emergency_service->poison_control_center : $langs->trans('NoData')
                ],
                'link'       => '<a href="../../admin/securityconf.php" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'      => 'fas fa-ambulance',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageEmergencyService . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName' => $langs->transnoentities('Emergency')
            ],
            'safety_rule' => [
                'label'      => [
                    $langs->transnoentities('ResponsibleToNotify') ?? '',
                    $langs->transnoentities('Phone') ?? '',
                    $langs->transnoentities('Location') ?? ''
                ],
                'content'    => [
                    dol_strlen($legalDisplay->emergency_service->safety_rule->responsible_for_preventing)       > 0 ? $legalDisplay->emergency_service->safety_rule->responsible_for_preventing       : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->emergency_service->safety_rule->phone)                            > 0 ? $legalDisplay->emergency_service->safety_rule->phone                            : $langs->trans('NoData'),
                    dol_strlen($legalDisplay->emergency_service->safety_rule->location_of_detailed_instruction) > 0 ? $legalDisplay->emergency_service->safety_rule->location_of_detailed_instruction : $langs->trans('NoData')
                ],
                'link'       => '<a href="../../admin/securityconf.php" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'      => 'fas fa-user-tie',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageSafetyRule . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName' => $langs->transnoentities('Emergency')
            ],
            'working_time' => [
                'label'      => [
                    $langs->transnoentities('Monday') ?? '',
                    $langs->transnoentities('Tuesday') ?? '',
                    $langs->transnoentities('Wednesday') ?? '',
                    $langs->transnoentities('Thursday') ?? '',
                    $langs->transnoentities('Friday') ?? '',
                    $langs->transnoentities('Saturday') ?? '',
                    $langs->transnoentities('Sunday') ?? ''
                ],
                'content'    => [
                    (dol_strlen($legalDisplay->working_hour->monday_morning)    > 0 or dol_strlen($legalDisplay->working_hour->monday_afternoon)    > 0) ? $legalDisplay->working_hour->monday_morning    . ' - ' . $legalDisplay->working_hour->monday_afternoon    : $langs->trans('NoData'),
                    (dol_strlen($legalDisplay->working_hour->tuesday_morning)   > 0 or dol_strlen($legalDisplay->working_hour->tuesday_afternoon)   > 0) ? $legalDisplay->working_hour->tuesday_morning   . ' - ' . $legalDisplay->working_hour->tuesday_afternoon   : $langs->trans('NoData'),
                    (dol_strlen($legalDisplay->working_hour->wednesday_morning) > 0 or dol_strlen($legalDisplay->working_hour->wednesday_afternoon) > 0) ? $legalDisplay->working_hour->wednesday_morning . ' - ' . $legalDisplay->working_hour->wednesday_afternoon : $langs->trans('NoData'),
                    (dol_strlen($legalDisplay->working_hour->thursday_morning)  > 0 or dol_strlen($legalDisplay->working_hour->thursday_afternoon)  > 0) ? $legalDisplay->working_hour->thursday_morning  . ' - ' . $legalDisplay->working_hour->thursday_afternoon  : $langs->trans('NoData'),
                    (dol_strlen($legalDisplay->working_hour->friday_morning)    > 0 or dol_strlen($legalDisplay->working_hour->friday_afternoon)    > 0) ? $legalDisplay->working_hour->friday_morning    . ' - ' . $legalDisplay->working_hour->friday_afternoon    : $langs->trans('NoData'),
                    (dol_strlen($legalDisplay->working_hour->saturday_morning)  > 0 or dol_strlen($legalDisplay->working_hour->saturday_afternoon)  > 0) ? $legalDisplay->working_hour->saturday_morning  . ' - ' . $legalDisplay->working_hour->saturday_afternoon  : $langs->trans('NoData'),
                    (dol_strlen($legalDisplay->working_hour->sunday_morning)    > 0 or dol_strlen($legalDisplay->working_hour->sunday_afternoon)    > 0) ? $legalDisplay->working_hour->sunday_morning    . ' - ' . $legalDisplay->working_hour->sunday_afternoon    : $langs->trans('NoData'),
                ],
                'link'       => '<a href="' . dol_buildpath('../admin/openinghours.php', 1) .'"" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'      => 'fas fa-clock',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageWorkingHour . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName' => $langs->transnoentities('Emergency')
            ],
            'information' => [
                'label'      => [
                    $langs->transnoentities('PermanentDerogation') ?? '',
                    $langs->transnoentities('OccasionalDerogation') ?? '',
                    $langs->transnoentities('DUER') ?? '',
                    $langs->transnoentities('ParticipationAgreement') ?? '',
                    $langs->transnoentities('Convention') ?? '',
                ],
                'content'    => [
                    dol_strlen($legalDisplay->derogation_schedule->permanent)                                   > 0 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>',
                    dol_strlen($legalDisplay->derogation_schedule->occasional)                                  > 0 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>',
                    dol_strlen($legalDisplay->DUER->how_access_to_duer)                                         > 0 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>',
                    dol_strlen($legalDisplay->participation_agreement->information_procedures)                  > 0 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>',
                    dol_strlen($legalDisplay->collective_agreement->location_and_access_terms_of_the_agreement) > 0 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>',
                ],
                'link'       => '<a href="../../admin/securityconf.php" target="_blank">' . $langs->trans('ConfigureSecurityAndSocialData') . ' <i class="fas fa-external-link-alt"></i></a>',
                'picto'      => 'fas fa-info',
                'progressBar' => '<div class="progress-group"><div class="progress sm"><div class="progress-bar progress-info" style="width: ' . $percentageParameters . '%;" title="0%"><div class="progress-bar progress-bar-consumed" style="width: 0%;" title="0%"></div></div></div></div>',
                'widgetName' => $langs->transnoentities('Emergency')
            ],
        ];

        return $dashboardData;
    }
}
