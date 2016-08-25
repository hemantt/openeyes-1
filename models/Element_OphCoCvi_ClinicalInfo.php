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

namespace OEModule\OphCoCvi\models;

/**
 * This is the model class for table "et_ophcocvi_clinicinfo".
 *
 * The followings are the available columns in table:
 * @property string $id
 * @property integer $event_id
 * @property string $examination_date
 * @property integer $is_considered_blind
 * @property integer $sight_varies_by_light_levels
 * @property string $unaided_right_va
 * @property string $unaided_left_va
 * @property string $best_corrected_right_va
 * @property string $best_corrected_left_va
 * @property string $best_corrected_binocular_va
 * @property integer $low_vision_status_id
 * @property integer $field_of_vision_id
 * @property string $diagnoses_not_covered
 * @property integer $consultant_id
 *
 * The followings are the available model relations:
 *
 * @property ElementType $element_type
 * @property EventType $eventType
 * @property Event $event
 * @property User $user
 * @property User $usermodified
 * @property OphCoCvi_ClinicalInfo_LowVisionStatus $low_vision_status
 * @property OphCoCvi_ClinicalInfo_FieldOfVision $field_of_vision
 * @property Element_OphCoCvi_ClinicalInfo_Disorders_Assignment[] $cvi_disorder_assignments
 * @property OphCoCvi_ClinicalInfo_Disorder[] $cvi_disorders
 * @property OphCoCvi_ClinicalInfo_Disorder[] $left_affected_cvi_disorders
 * @property OphCoCvi_ClinicalInfo_Disorder[] $right_affected_cvi_disorders
 * @property Element_OphCoCvi_ClinicalInfo_Disorder_Section_Comments[] $cvi_disorder_section_comments
 * @property User $consultant
 */

class Element_OphCoCvi_ClinicalInfo extends \BaseEventTypeElement
{
    public static $BLIND_STATUS = "Severely Sight Impaired";
    public static $NOT_BLIND_STATUS = "Sight Impaired";

    /**
     * Returns the static model of the specified AR class.
     * @return the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'et_ophcocvi_clinicinfo';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array(
                'event_id, examination_date, is_considered_blind, sight_varies_by_light_levels, unaided_right_va, unaided_left_va, best_corrected_right_va, best_corrected_left_va, best_corrected_binocular_va, low_vision_status_id, field_of_vision_id, diagnoses_not_covered, consultant_id, ',
                'safe'
            ),
            array('examination_date', 'OEDateValidatorNotFuture'),
            array('is_considered_blind', 'boolean'),
            array('unaided_right_va, unaided_left_va, best_corrected_right_va, best_corrected_left_va, best_corrected_binocular_va', 'length', 'max' => 20),
            array('examination_date, is_considered_blind, sight_varies_by_light_levels, unaided_right_va, unaided_left_va, best_corrected_right_va, best_corrected_left_va, best_corrected_binocular_va, low_vision_status_id, field_of_vision_id', 'required', 'on' => 'finalise'),
            array(
                'id, event_id, examination_date, is_considered_blind, sight_varies_by_light_levels, unaided_right_va, unaided_left_va, best_corrected_right_va, best_corrected_left_va, best_corrected_binocular_va, low_vision_status_id, field_of_vision_id, diagnoses_not_covered, consultant_id, ',
                'safe',
                'on' => 'search'
            ),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'element_type' => array(
                self::HAS_ONE,
                'ElementType',
                'id',
                'on' => "element_type.class_name='" . get_class($this) . "'"
            ),
            'eventType' => array(self::BELONGS_TO, 'EventType', 'event_type_id'),
            'event' => array(self::BELONGS_TO, 'Event', 'event_id'),
            'user' => array(self::BELONGS_TO, 'User', 'created_user_id'),
            'usermodified' => array(self::BELONGS_TO, 'User', 'last_modified_user_id'),
            'low_vision_status' => array(
                self::BELONGS_TO,
                'OEModule\OphCoCvi\models\OphCoCvi_ClinicalInfo_LowVisionStatus',
                'low_vision_status_id'
            ),
            'field_of_vision' => array(
                self::BELONGS_TO,
                'OEModule\OphCoCvi\models\OphCoCvi_ClinicalInfo_FieldOfVision',
                'field_of_vision_id'
            ),
            'cvi_disorder_assignments' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\Element_OphCoCvi_ClinicalInfo_Disorder_Assignment',
                'element_id'
            ),
            'left_cvi_disorder_assignments' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\Element_OphCoCvi_ClinicalInfo_Disorder_Assignment',
                'element_id',
                'on' => 'left_cvi_disorder_assignments.eye_id = ' . \Eye::LEFT
            ),
            'right_cvi_disorder_assignments' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\Element_OphCoCvi_ClinicalInfo_Disorder_Assignment',
                'element_id',
                'on' => 'right_cvi_disorder_assignments.eye_id = ' . \Eye::RIGHT
            ),
            'cvi_disorders' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\OphCoCvi_ClinicalInfo_Disorder', 'ophcocvi_clinicinfo_disorder_id',
                'through' => 'cvi_disorder_assignments'
            ),
            'left_cvi_disorders' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\OphCoCvi_ClinicalInfo_Disorder', 'ophcocvi_clinicinfo_disorder_id',
                'through' => 'cvi_disorder_assignments',
                'on' => 'cvi_disorder_assignments.eye_id = ' . \Eye::LEFT
            ),
            'right_cvi_disorders' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\OphCoCvi_ClinicalInfo_Disorder', 'ophcocvi_clinicinfo_disorder_id',
                'through' => 'cvi_disorder_assignments',
                'on' => 'cvi_disorder_assignments.eye_id = ' . \Eye::RIGHT
            ),
            'left_affected_cvi_disorders' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\OphCoCvi_ClinicalInfo_Disorder', 'ophcocvi_clinicinfo_disorder_id',
                'through' => 'cvi_disorder_assignments',
                'on' => 'cvi_disorder_assignments.eye_id = ' . \Eye::LEFT . ' AND cvi_disorder_assignments.affected = 1'
            ),
            'right_affected_cvi_disorders' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\OphCoCvi_ClinicalInfo_Disorder', 'ophcocvi_clinicinfo_disorder_id',
                'through' => 'cvi_disorder_assignments',
                'on' => 'cvi_disorder_assignments.eye_id = ' . \Eye::RIGHT . ' AND cvi_disorder_assignments.affected = 1'
            ),
            'cvi_disorder_section_comments' => array(
                self::HAS_MANY,
                'OEModule\OphCoCvi\models\Element_OphCoCvi_ClinicalInfo_Disorder_Section_Comments', 'element_id'
            ),
            'consultant' => array(self::BELONGS_TO, 'User', 'consultant_id'),

        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'event_id' => 'Event',
            'examination_date' => 'Examination date',
            'is_considered_blind' => 'I consider this person is',
            'sight_varies_by_light_levels' => 'Sight varies by light levels',
            'unaided_right_va' => 'Unaided right VA',
            'unaided_left_va' => 'Unaided left VA',
            'best_corrected_right_va' => 'Best corrected right VA',
            'best_corrected_left_va' => 'Best corrected left VA',
            'best_corrected_binocular_va' => 'Best corrected binocular VA',
            'low_vision_status_id' => 'Low vision status',
            'field_of_vision_id' => 'Field of vision',
            'disorders' => 'Disorders',
            'diagnoses_not_covered' => 'Diagnosis not covered in any of the above',
            'consultant_id' => 'Consultant',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('event_id', $this->event_id, true);
        $criteria->compare('examination_date', $this->examination_date);
        $criteria->compare('is_considered_blind', $this->is_considered_blind);
        $criteria->compare('sight_varies_by_light_levels', $this->sight_varies_by_light_levels);
        $criteria->compare('unaided_right_va', $this->unaided_right_va);
        $criteria->compare('unaided_left_va', $this->unaided_left_va);
        $criteria->compare('best_corrected_right_va', $this->best_corrected_right_va);
        $criteria->compare('best_corrected_left_va', $this->best_corrected_left_va);
        $criteria->compare('best_corrected_binocular_va', $this->best_corrected_binocular_va);
        $criteria->compare('low_vision_status_id', $this->low_vision_status_id);
        $criteria->compare('field_of_vision_id', $this->field_of_vision_id);
        $criteria->compare('disorders', $this->disorders);
        $criteria->compare('diagnoses_not_covered', $this->diagnoses_not_covered);
        $criteria->compare('consultant_id', $this->consultant_id);

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
        ));
    }

    /**
     * @param Element_OphCoCvi_ClinicalInfo_Disorder_Assignment $assignment
     * @param $data
     * @throws \Exception
     */
    private function updateDisorderAssignment(Element_OphCoCvi_ClinicalInfo_Disorder_Assignment $assignment, $data)
    {
        $assignment->element_id = $this->id;
        $assignment->affected = isset($data['affected']) ? (boolean)$data['affected'] : false;
        $assignment->main_cause = isset($data['main_cause']) ? (boolean)$data['main_cause'] : false;

        if (!$assignment->save()) {
            throw new \Exception('Unable to save CVI Disorder Assignment: ' . print_r($assignment->getErrors(), true));
        }
    }

    /**
     * Update the CVI Disorder status for this element.
     *
     * @param $side
     * @param $data
     * @throws \Exception
     */
    public function updateDisorders($side, $data)
    {
        if (!in_array($side, array('left', 'right'))) {
            throw new \Exception("invalid side specification");
        }

        $eye_id = $side == 'left' ? \Eye::LEFT : \Eye::RIGHT;

        // ensure we're manipulating what is currently in the db
        $current = $this->getRelated($side . "_cvi_disorder_assignments", true);

        // if the element has been saved before, then the assignment values will exist
        // and we can update, or delete those that are no longer required.
        while($assignment = array_shift($current)) {
            if (array_key_exists($assignment->ophcocvi_clinicinfo_disorder_id, $data)) {
                $this->updateDisorderAssignment($assignment, $data[$assignment->ophcocvi_clinicinfo_disorder_id]);
                unset($data[$assignment->ophcocvi_clinicinfo_disorder_id]);
            }
            else {
                if (!$assignment->delete()) {
                    throw new \Exception('Unable to delete CVI Disorder Assignment: ' . print_r($assignment->getErrors(), true));
                }
            }
        }

        // create new assignments that don't yet exist for the element
        foreach ($data as $cvi_disorder_id => $values) {
            $assignment = new Element_OphCoCvi_ClinicalInfo_Disorder_Assignment();
            $assignment->eye_id = $eye_id;
            $assignment->ophcocvi_clinicinfo_disorder_id = $cvi_disorder_id;

            $this->updateDisorderAssignment($assignment, $values);
        }
    }



    /**
     * Set the section comments on the element.
     *
     * @param $data
     * @throws \CDbException
     * @throws \Exception
     */
    public function updateDisorderSectionComments($data)
    {
        $current = $this->getRelated('cvi_disorder_section_comments', true);

        while ($section_comment = array_shift($current)) {
            if (array_key_exists($section_comment->ophcocvi_clinicinfo_disorder_section_id, $data)) {
                $comment_data = $data[$section_comment->ophcocvi_clinicinfo_disorder_section_id];
                $section_comment->comments = isset($comment_data['comments']) ? $comment_data['comments'] : "";
                if (!$section_comment->save()) {
                    throw new \Exception('Unable to save CVI Disorder Section Comment: ' . print_r($section_comment->getErrors(), true));
                }
                unset($data[$section_comment->ophcocvi_clinicinfo_disorder_section_id]);
            }
            else {
                if (!$section_comment->delete()) {
                    throw new \Exception('Unable to delete CVI Disorder Section Comment: ' . print_r($section_comment->getErrors(), true));
                }
            }
        }

        foreach ($data as $section_id => $values) {
            $section_comment = new Element_OphCoCvi_ClinicalInfo_Disorder_Section_Comments();
            $section_comment->ophcocvi_clinicinfo_disorder_section_id = $section_id;
            $section_comment->comments = isset($values['comments']) ? $values['comments'] : "";
            $section_comment->element_id = $this->id;
            if (!$section_comment->save()) {
                throw new \Exception("Unable to save CVI Disorder Section Comment: " . print_r($section_comment->getErrors(), true));
            }
        }
    }

    /**
     * @return string
     */
    public function getDisplayStatus()
    {
        return $this->is_considered_blind ? static::$BLIND_STATUS : static::$NOT_BLIND_STATUS;
    }

    /**
     * To generate the low vision status array for the pdf
     *
     * @return array
     */
    public function generateFieldOfVision() {
        $data = array();
        $field_of_vision_statuses = (OphCoCvi_ClinicalInfo_FieldOfVision::model()->findAll(array('order' => 'display_order asc')));
        foreach($field_of_vision_statuses as $field_of_vision_status) {
            $key = $field_of_vision_status->name;
            $data[] = array($key,($this->field_of_vision === $field_of_vision_status->id) ? 'X' : '');
        }
        return $data;
    }

    /**
     * To generate the low vision status array for the pdf
     *
     * @return array
     */
    public function generateLowVisionStatus() {
        $data = array();
        $low_vision_statuses = (OphCoCvi_ClinicalInfo_LowVisionStatus::model()->findAll(array('order' => 'display_order asc')));
        foreach($low_vision_statuses as $low_vision_status) {
            $key = $low_vision_status->name;
            $data[] = array($key,($this->low_vision_status_id === $low_vision_status->id) ? 'X' : '');
        }
        return $data;
    }

    public function getDisordersForSection($disorder_section, $flag) {
        $data = array();
        if($flag == 0) {
            $data[] = array('','','','','');
        }
        $first = 1;

        foreach (OphCoCvi_ClinicalInfo_Disorder::model()
                     ->findAll('`active` = ? and section_id = ?',array(1, $disorder_section->id)) as $disorder) {
            $disorder_section_name = '';
            if($first === 1) {
                $disorder_section_name = $disorder_section->name;
                $first = 0;
            }
            $value_right = Element_OphCoCvi_ClinicalInfo_Disorder_Assignment::model()
                ->getDisorderAffectedStatus($disorder->id,$this->id,'right');
            $value_left = Element_OphCoCvi_ClinicalInfo_Disorder_Assignment::model()
                ->getDisorderAffectedStatus($disorder->id,$this->id,'left');
            $data[] = array($disorder_section_name,$disorder->name,$disorder->code, !empty($value_right) ? 'Yes' : 'No',
                !empty($value_left) ? 'Yes' : 'No');
        }
        if($disorder_section->comments_allowed == 1) {
            $comments = Element_OphCoCvi_ClinicalInfo_Disorder_Section_Comments::model()->
                getDisorderSectionComments($disorder_section->id,$this->id);
            $data[] = array('', $disorder_section->comments_label.' : '.$comments, '','','');
        }
        
        return $data;
    }

    /**
     * Returns an associative array of the data values for printing
     */
    public function getStructuredDataForPrint()
    {
        $result = array();
        $isConsideredBlindNo = $isConsideredBlindYes = '';
        $varyByLightLevelsYes = $varyByLightLevelsNo = '';
        $result['examinationDate'] = date('d/m/Y', strtotime($this->examination_date));
        if((int)$this->is_considered_blind === 0) { $isConsideredBlindNo = 'X';}
        if((int)$this->is_considered_blind === 1) { $isConsideredBlindYes = 'X';}
        $result['isConsideredBlind'] = array(
            array('', $isConsideredBlindNo, ''),
            array('', $isConsideredBlindYes,''),
        );
        $result['visualAcuity'] = array(
            array('','',''),
            array('Unaided',$this->unaided_right_va, $this->unaided_left_va),
            array('Best corrected',$this->best_corrected_right_va, $this->best_corrected_left_va),
            array('Best corrected with both eyes',$this->best_corrected_binocular_va, ''),
        );
        if($this->consultant) {
            $result['consultantName'] = $this->consultant->getFullName();
        }
        if((int)$this->sight_varies_by_light_levels === 1) { $varyByLightLevelsYes = 'X';}
        if((int)$this->sight_varies_by_light_levels === 0) { $varyByLightLevelsNo = 'X';}
        $result['varyByLightLevels'][0] = array('',$varyByLightLevelsYes,'',$varyByLightLevelsNo);
        $fieldOfVisionData = $this->generateFieldOfVision();
        $lowVisionData = array_merge(array(0=>array('','')),$this->generateLowVisionStatus());

        $result['fieldOfVisionAndLowVisionStatus'][0] = array('','','','');
        for($k=0;$k<sizeof($fieldOfVisionData);$k++){
            $result["fieldOfVisionAndLowVisionStatus"][$k+1] = array_merge($fieldOfVisionData[$k],$lowVisionData[$k]);
        }

        $result['sightVariesByLightLevelYes'] = ($this->sight_varies_by_light_levels === 1) ? 'X' : '';
        $result['sightVariesByLightLevelNo'] = ($this->sight_varies_by_light_levels === 0) ? '' : 'X';
        $flag = 0;
        foreach(OphCoCvi_ClinicalInfo_Disorder_Section::model()
                    ->findAll('`active` = ?',array(1)) as $disorder_section) {
            $result['disorder' . ucfirst($disorder_section->name) . 'Table'] =
                $this->getDisordersForSection($disorder_section, $flag);
            $flag = 1;
        }
        $result['diagnosisNotCovered'] = $this->diagnoses_not_covered;
        return $result;
    }

    /**
     * @param $section
     * @return Element_OphCoCvi_ClinicalInfo_Disorder_Section_Comments
     */
    public function getDisorderSectionComment($section)
    {
        foreach ($this->cvi_disorder_section_comments as $comment) {
            if ($section->id == $comment->ophcocvi_clinicinfo_disorder_section_id) {
                return $comment;
            }
        }
    }

    /**
     * @param OphCoCvi_ClinicalInfo_Disorder $cvi_disorder
     * @param string $side left or right
     * @return boolean
     * @throws \Exception
     */
    public function hasCviDisorderForSide(OphCoCvi_ClinicalInfo_Disorder $cvi_disorder, $side)
    {
        if (!in_array($side, array('left', 'right'))) {
            throw new \Exception("invalid side attribute");
        }
        foreach ($this->{$side . '_cvi_disorder_assignments'} as $recorded_cvi) {
            if ($recorded_cvi->ophcocvi_clinicinfo_disorder_id == $cvi_disorder->id) {
                return $recorded_cvi->affected;
            }
        }
    }

    /**
     * @param OphCoCvi_ClinicalInfo_Disorder $cvi_disorder
     * @param $side
     * @return mixed
     * @throws \Exception
     */
    public function isCviDisorderMainCauseForSide(OphCoCvi_ClinicalInfo_Disorder $cvi_disorder, $side)
    {
        if (!in_array($side, array('left', 'right'))) {
            throw new \Exception("invalid side attribute");
        }
        foreach ($this->{$side . '_cvi_disorder_assignments'} as $recorded_cvi) {
            if ($recorded_cvi->ophcocvi_clinicinfo_disorder_id == $cvi_disorder->id) {
                return $recorded_cvi->main_cause;
            }
        }
    }
}