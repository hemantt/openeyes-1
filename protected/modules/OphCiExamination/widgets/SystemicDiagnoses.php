<?php
/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2017
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2017, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */

namespace OEModule\OphCiExamination\widgets;

use OEModule\OphCiExamination\models\SystemicDiagnoses as SystemicDiagnosesElement;
use OEModule\OphCiExamination\models\SystemicDiagnoses_Diagnosis;

class SystemicDiagnoses extends \BaseEventElementWidget
{
    /**
     * @var SystemicDiagnosesElement
     */
    public $element;

    /**
     * @return SystemicDiagnosesElement
     */
    protected function getNewElement()
    {
        return new SystemicDiagnosesElement();
    }

    /**
     * Pre-process to determine whether the element should be updating the patient level
     * information or not.
     *
     * @inheritdoc
     */
    protected function setElementFromDefaults()
    {
        $this->element->storePatientUpdateStatus();
        parent::setElementFromDefaults();
    }

    /**
     * Returns the required Disorders (Systemic Diagnoses)
     * @return array of Disorders
     */
    public function getRequiredSystemicDiagnoses()
    {
        $exam_api = \Yii::app()->moduleAPI->get('OphCiExamination');
        return $exam_api->getRequiredSystemicDiagnoses($this->patient);
    }

    /**
     * Gets the required and missing Disorders(Systemic Diagnoses)
     * @return array of SystemicDiagnoses_Diagnosis
     */
    public function getMissingRequiredSystemicDiagnoses()
    {
        $current_ids = array_map(function ($e) { return $e->disorder_id; }, array_merge($this->element->diagnoses, $this->element->checked_required_diagnoses));
        $missing = array();

        foreach ($this->getRequiredSystemicDiagnoses() as $required) {
            if (!in_array($required->id, $current_ids)) {
                $entry = new SystemicDiagnoses_Diagnosis();
                $entry->disorder_id = $required->id;
                $missing[] = $entry;
            }
        }
        return $missing;
    }

    /**
     * Returns all Systemic Diagnoses that were previously marked with "not" status
     * @return SystemicDiagnoses_Diagnosis[]
     */

    public function getCheckedRequiredSystemicDiagnoses()
    {
        $current_disorder_ids = array_map(function ($e) { return $e->disorder_id; }, array_merge($this->element->diagnoses));
        $checked = array();
        foreach ($this->element->checked_required_diagnoses as $diag) {
            if(!in_array($diag->disorder_id, $current_disorder_ids)) {
                $row = new SystemicDiagnoses_Diagnosis();
                $row->disorder_id = $diag->disorder_id;
                $row->side_id = $diag->side_id;
                $row->date = $diag->date;
                $row->has_disorder = $diag->has_disorder;
                $checked[] = $row;
            }
        }
        return $checked;
    }

    public function getDiagnosesViewMode()
    {
        $ret = array();
        $present = $this->element->orderedDiagnoses;
        $required_checked = $this->getCheckedRequiredSystemicDiagnoses();
        $required_not_checked = $this->getMissingRequiredSystemicDiagnoses();

        $ret[] = implode(" // ", $present);
        if(!empty($required_checked)) {
            $ret[] = implode(" // ", array_map(function($e){ return '<strong>Not present: </strong>'. $e->disorder->term; }, $required_checked));
        }
        if(!empty($required_not_checked)) {
            $ret[] = implode(" // ", array_map(function($e){ return '<strong>Not checked: </strong>'. $e->disorder->term; }, $required_not_checked));
        }

        return implode(" // ", $ret);
    }

    /**
     * @param SystemicDiagnosesElement $element
     * @param $data
     * @throws \CException
     */
    protected function updateElementFromData($element, $data)
    {
        if  (!is_a($element, 'OEModule\OphCiExamination\models\SystemicDiagnoses')) {
            throw new \CException('invalid element class ' . get_class($element) . ' for ' . static::class);
        }

        // Ensure we track whether to update the secondary diagnoses for the patient
        // or not when we save this element.
        $element->storePatientUpdateStatus();

        // pre-cache current entries so any entries that remain in place will use the same db row
        $entries_by_id = array();
        foreach ($element->diagnoses as $entry) {
            $entries_by_id[$entry->id] = $entry;
        }

        if (array_key_exists('entries', $data)) {
            $entries = array();
            foreach ($data['entries'] as $entry_data) {

                $id = array_key_exists('id', $entry_data) ? $entry_data['id'] : null;
                $entry = ($id && array_key_exists($id, $entries_by_id)) ?
                    $entries_by_id[$id] :
                    new SystemicDiagnoses_Diagnosis();

                $entry->disorder_id = $entry_data['disorder_id'];
                $entry->side_id = $this->getEyeIdFromPost($entry_data);
                $entry->date = $entry_data['date'];
                $entry->has_disorder = $entry_data['has_disorder'];

                $entries[] = $entry;
            }

            $element->diagnoses = $entries;
        } else {
            $element->diagnoses = array();
        }
    }

    /**
     * Checks if there was a posted has_disoder value
     * @param $row
     * @return int 0|1|-9 if posted oterwise NULL
     */
    public function getPostedCheckedStatus($row)
    {
        $value = \Helper::elementFinder(\CHtml::modelName($this->element) . ".has_disorder.$row", $_POST);
        return ( is_numeric($value) ? $value : null);
    }

    public function getPostedNaEye($row)
    {
        $value = \Helper::elementFinder(\CHtml::modelName($this->element) . ".na_eye.$row", $_POST);
        return ( is_numeric($value) ? $value : null);
    }


    public function getEyeIdFromPost(array $data)
    {
        return \Helper::getEyeIdFromArray($data);
    }
}