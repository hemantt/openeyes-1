<?php
/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2016
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2016, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */


namespace OEModule\OphCoCvi\components;

use OEModule\OphCoCvi\models\Element_OphCoCvi_ClericalInfo;
use OEModule\OphCoCvi\models\Element_OphCoCvi_ClinicalInfo;
use OEModule\OphCoCvi\models\Element_OphCoCvi_EventInfo;

class OphCoCvi_Manager extends \CComponent
{
    public static $CVI_COMPLETE = 1;
    public static $CVI_CLINICAL_COMPLETE = 2;
    public static $CVI_CLERICAL_COMPLETE = 3;
    public static $CVI_INCOMPLETE = 4;
    public static $ISSUED = 5;

    /**
     * @param $status
     * @return string
     */
    public function getStatusText($status)
    {
        $lookup = array(
            self::$ISSUED => "Issued",
            self::$CVI_COMPLETE => "Complete",
            self::$CVI_CLINICAL_COMPLETE => 'Clinically Complete',
            self::$CVI_CLERICAL_COMPLETE => 'Clerically Complete',
            self::$CVI_INCOMPLETE => 'Incomplete',
        );
        if (isset($lookup[$status])) {
            return $lookup[$status];
        }

        return "Unrecognised Status";
    }

    protected $yii;
    /**
     * @var \EventType
     */
    protected $event_type;

    public function __construct(\CApplication $yii = null, \EventType $event_type = null)
    {
        if (is_null($yii)) {
            $yii = \Yii::app();
        }

        if (is_null($event_type)) {
            $event_type = $this->determineEventType();
        }
        $this->event_type = $event_type;

        $this->yii = $yii;
    }

    /**
     * Returns the non-namespaced module class of the module API Instance
     *
     * @return mixed
     */
    protected function getModuleClass()
    {
        $namespace_pieces = explode("\\", __NAMESPACE__);
        return $namespace_pieces[1];
    }

    /**
     * @return \EventType
     * @throws \Exception
     */
    protected function determineEventType()
    {
        $module_class = $this->getModuleClass();

        if (!$event_type = \EventType::model()->find('class_name=?', array($module_class))) {
            throw new \Exception("Module is not migrated: $module_class");
        }
        return $event_type;
    }

    /**
     * Wrapper for starting a transaction.
     *
     * @return \CDbTransaction|null
     */
    protected function startTransaction()
    {
        return $this->yii->db->getCurrentTransaction() === null
            ? $this->yii->db->beginTransaction()
            : null;
    }

    /**
     * @param \Patient $patient
     * @return \Event[]
     */
    public function getEventsForPatient(\Patient $patient)
    {
        return \Event::model()->getEventsOfTypeForPatient($this->event_type, $patient);
    }

    protected $info_element_for_events = array();

    /**
     * @param $event
     * @param $element_class
     * @return \CActiveRecord|null
     */
    protected function getElementForEvent($event, $element_class)
    {
        $core_class = 'Element_OphCoCvi_EventInfo';
        $namespaced_class = '\\OEModule\OphCoCvi\\models\\' . $core_class;

        $cls_rel_map = array(
            'Element_OphCoCvi_ClinicalInfo' => 'clinical_element',
            'Element_OphCoCvi_ClericalInfo' => 'clerical_element',
            'Element_OphCoCvi_ConsentSignature' => 'consent_element',
        );

        if (!isset($this->info_element_for_events[$event->id])) {
            $this->info_element_for_events[$event->id] = $namespaced_class::model()->with(array_values($cls_rel_map))->findByAttributes(array('event_id' => $event->id));
        }

        if (array_key_exists($element_class, $cls_rel_map)) {
            return $this->info_element_for_events[$event->id]->{$cls_rel_map[$element_class]};
        } elseif ($element_class == $core_class) {
            return $this->info_element_for_events[$event->id];
        }
    }

    /**
     * Convenience wrapper to clear out element data when put into specific states that we don't want to keep
     *
     * @TODO this might not be necessary if there's a sensible way to clear out the validation state of an ActiveRecord
     * @param \Event $event
     */
    protected function resetElementStore(\Event $event = null)
    {
        if ($event) {
            unset($this->info_element_for_events[$event->id]);
        } else {
            $this->info_element_for_events = array();
        }
    }

    /**
     * Wrapper to insert missing elements for a CVI event if they haven't been
     * created (due to access restrictions)
     *
     * NB The inserted elements may be removed in the view context if the user still
     * doesn't have the right to manage the data for that specific element.
     *
     * @param \Event $event
     * @param bool $for_editing
     * @return array|\BaseEventTypeElement[]
     */
    public function getEventElements(\Event $event, $for_editing = false)
    {
        if (!$for_editing) {
            return $event->getElements();
        } else {
            $default = $event->eventType->getDefaultElements();
            $current = $event->getElements();
            if (count($current) == $default) {
                // assume a match implies all the elements are already recorded for the event.
                return $current;
            }

            $editable = array();
            foreach ($default as $el) {
                // check there's something in the current list as might be the last default elements that are not defined
                if (isset($current[0]) && get_class($el) == get_class($current[0])) {
                    // the order should be consistent across default and current.
                    $editable[] = array_shift($current);
                } else {
                    $editable[] = $el;
                }
            }
            return $editable;
        }
    }

    /**
     * @param \Event $event
     * @return null|Element_OphCoCvi_EventInfo
     */
    public function getEventInfoElementForEvent(\Event $event)
    {
        return $this->getElementForEvent($event, 'Element_OphCoCvi_EventInfo');
    }

    /**
     * @param \Event $event
     * @return null|Element_OphCoCvi_ClinicalInfo
     */
    public function getClinicalElementForEvent(\Event $event)
    {
        return $this->getElementForEvent($event, 'Element_OphCoCvi_ClinicalInfo');
    }

    /**
     * @param \Event $event
     * @return null|Element_OphCoCvi_ClericalInfo
     */
    public function getClericalElementForEvent(\Event $event)
    {
        return $this->getElementForEvent($event, 'Element_OphCoCvi_ClericalInfo');
    }

    /**
     * @param \Event $event
     * @return null|Element_OphCoCvi_ConsentSignature
     */
    public function getConsentSignatureElement(\Event $event)
    {
        return $this->getElementForEvent($event, 'Element_OphCoCvi_ConsentSignature');
    }

    /**
     * Generate the text display of the status of the CVI
     *
     * @param Element_OphCoCvi_ClinicalInfo $clinical
     * @param Element_OphCoCvi_ClericalInfo $clerical
     * @return string
     */
    protected function getDisplayStatus(Element_OphCoCvi_ClinicalInfo $clinical, Element_OphCoCvi_EventInfo $info)
    {
        return $clinical->getDisplayStatus() . ' (' . $info->getIssueStatusForDisplay() . ')';
    }

    /**
     * @param \Event $event
     * @return string
     */
    public function getDisplayStatusForEvent(\Event $event)
    {
        $clinical = $this->getClinicalElementForEvent($event);
        $info = $this->getEventInfoElementForEvent($event);

        return $this->getDisplayStatus($clinical, $info);
    }

    /**
     * @param Element_OphCoCvi_EventInfo $element
     * @return string
     */
    public function getDisplayStatusFromEventInfo(Element_OphCoCvi_EventInfo $element)
    {
        return $this->getDisplayStatus($element->clinical_element, $element);
    }

    /**
     * @param \Event $event
     * @return string|null
     */
    public function getDisplayStatusDateForEvent(\Event $event)
    {
        $clinical = $this->getClinicalElementForEvent($event);
        return $clinical->examination_date;
    }

    /**
     * @param \Event $event
     * @return mixed|null
     */
    public function getDisplayIssueDateForEvent(\Event $event)
    {
        $info = $this->getEventInfoElementForEvent($event);
        return $info->getIssueDateForDisplay();
    }

    /**
     * @param \Event $event
     * @return string
     */
    public function getEventViewUri(\Event $event)
    {
        return $this->yii->createUrl($event->eventType->class_name . '/default/view/' . $event->id);
    }

    /**
     * @param \Event $event
     * @return string
     */
    public function getTitle(\Event $event)
    {
        $title = $event->eventType->name;

        if ($event->info) {
            // this should always be set.
            $title .= ' - ' . $event->info;
        }

        return $title;
    }

    /**
     * @param Element_OphCoCvi_EventInfo $event_info
     * @return \User|null
     */
    public function getClinicalConsultant(Element_OphCoCvi_EventInfo $event_info)
    {
        /**
         * @var Element_OphCoCvi_ClinicalInfo
         */
        if ($clinical = $event_info->clinical_element) {
            return $clinical->consultant;
        }
        return null;
    }

    /**
     * @param \Event $event
     * @return bool
     */
    public function canIssueCvi(\Event $event)
    {
        if ($info = $this->getEventInfoElementForEvent($event)) {
            if (!$info->is_draft) {
                return false;
            }
        } else {
            return false;
        }

        if ($clinical = $this->getClinicalElementForEvent($event)) {
            $clinical->setScenario('finalise');
            if (!$clinical->validate()) {
                return false;
            }
        } else {
            return false;
        }

        if ($clerical = $this->getClericalElementForEvent($event)) {
            $clerical->setScenario('finalise');
            if (!$clerical->validate()) {
                return false;
            }
        } else {
            return false;
        }

        if ($signature = $this->getConsentSignatureElement($event)) {
            if (!$signature->checkSignature()) {
                return false;
            }
        } else {
            return false;
        }


        return true;
    }

    /**
     * @param \Event $event
     * @return bool
     */
    public function canEditEvent(\Event $event)
    {
        if ($info_element = $this->getEventInfoElementForEvent($event)) {
            return $info_element->is_draft;
        }
        return false;
    }

    public function issueCvi(\Event $event, $user_id)
    {
        // begin transaction
        $transaction = $this->startTransaction();

        try {
            // TODO: generate the PDF

            // set the status of the event to complete and assign the PDF to the event
            $info_element = $this->getEventInfoElementForEvent($event);
            $info_element->is_draft = false;
            $info_element->save();

            $event->info = $this->getStatusText(self::$ISSUED);
            $event->save();

            $event->audit('event', 'cvi-issued', null, 'CVI Issued', array('user_id' => $user_id));

            if ($transaction) {
                $transaction->commit();
            }
            return true;
        } catch (\Exception $e) {
            if ($transaction) {
                $transaction->rollback();
            }
        }
        return false;

    }

    /**
     * @param \Event $event
     * @return bool
     */
    public function isIssued(\Event $event)
    {
        $info_element = $this->getEventInfoElementForEvent($event);
        return !$info_element->is_draft;
    }

    /**
     * @param \Event $event
     * @return mixed
     */
    public function calculateStatus(\Event $event)
    {
        if ($clerical = $this->getClericalElementForEvent($event)) {
            $clerical->setScenario('finalise');
            $clerical_complete = $clerical->validate();
        } else {
            $clerical_complete = false;
        }


        if ($clinical = $this->getClinicalElementForEvent($event)) {
            $clinical->setScenario('finalise');
            $clinical_complete = $clinical->validate();
        } else {
            $clinical_complete = false;
        }

        $this->resetElementStore($event);

        if ($clerical_complete && $clinical_complete) {
            return self::$CVI_COMPLETE;
        }
        if ($clinical_complete) {
            return self::$CVI_CLINICAL_COMPLETE;
        }
        if ($clerical_complete) {
            return self::$CVI_CLERICAL_COMPLETE;
        }

        return self::$CVI_INCOMPLETE;
    }


    /**
     * @param \CDbCriteria $criteria
     * @param array $filter
     */
    private function handleDateRangeFilter(\CDbCriteria $criteria, $filter = array())
    {
        $from = null;
        if (isset($filter['date_from'])) {
            $from = \Helper::convertNHS2MySQL($filter['date_from']);
        }
        $to = null;
        if (isset($filter['date_to'])) {
            $to = \Helper::convertNHS2MySQL($filter['date_to']);
        }
        if ($from && $to) {
            if ($from > $to) {
                $criteria->addBetweenCondition('event.event_date', $to, $from);
            } else {
                $criteria->addBetweenCondition('event.event_date', $from, $to);
            }
        } elseif ($from) {
            $criteria->addCondition('event.event_date >= ?');
            $criteria->params[] = $from;
        } elseif ($to) {
            $criteria->addCondition('event.event_date <= ?');
            $criteria->params[] = $to;
        }
    }

    /**
     * @param \CDbCriteria $criteria
     * @param array $filter
     */
    private function handleConsultantListFilter(\CDbCriteria $criteria, $filter = array())
    {
        if (isset($filter['consultant_ids']) && strlen(trim($filter['consultant_ids']))) {
            $criteria->addInCondition('clinical_element.consultant_id', explode(",", $filter['consultant_ids']));
        }
    }

    /**
     * @param \CDbCriteria $criteria
     * @param array $filter
     */
    private function handleIssuedFilter(\CDbCriteria $criteria, $filter = array())
    {
        if (!isset($filter['show_issued']) || (isset($filter['show_issued']) && !(bool)$filter['show_issued'])) {
            $criteria->addCondition('t.is_draft = ?');
            $criteria->params[] = true;
        }
    }

    /**
     * @param array $filter
     * @return \CDbCriteria
     */
    protected function buildFilterCriteria($filter = array())
    {
        $criteria = new \CDbCriteria();

        $this->handleDateRangeFilter($criteria, $filter);
        $this->handleConsultantListFilter($criteria, $filter);
        $this->handleIssuedFilter($criteria, $filter);
        return $criteria;
    }

    /**
     * Abstraction of the list provider
     *
     * @param array $filter
     * @return \CActiveDataProvider
     */
    public function getListDataProvider($filter = array())
    {
        $model = Element_OphCoCvi_EventInfo::model()->with(
            'clinical_element',
            'clinical_element.consultant',
            'clerical_element',
            'event.episode.patient.contact');

        $sort = new \CSort();

        $sort->attributes = array(
            'event_date' => array(
                'asc' => 'event.event_date asc, event.id asc',
                'desc' => 'event.event_date desc, event.id desc',
            ),
            'patient_name' => array(
                'asc' => 'lower(contact.last_name) asc, lower(contact.first_name) asc',
                'desc' => 'lower(contact.last_name) desc, lower(contact.first_name) desc',
            ),
            'consultant' => array(
                'asc' => 'lower(consultant.last_name) asc, lower(consultant.first_name) asc, event.id asc',
                'desc' => 'lower(consultant.last_name) desc, lower(consultant.first_name) desc, event.id desc',
            ),
            'issue_status' => array('asc' => 'is_draft desc, event.id asc', 'desc' => 'is_draft asc, event.id desc'),
            // no specific issue date field
            // TODO: retrieve the date attribute from the info element class
            'issue_date' => array(
                'asc' => 'is_draft asc, t.last_modified_date asc',
                'desc' => 'is_draft asc, t.last_modified_date desc'
            ),
        );

        $criteria = $this->buildFilterCriteria($filter);

        return new \CActiveDataProvider($model, array(
            'sort' => $sort,
            'criteria' => $criteria
        ));
    }

}