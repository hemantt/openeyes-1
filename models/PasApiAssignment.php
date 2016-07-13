<?php

namespace OEModule\PASAPI\models;

/**
 * OpenEyes.
 *
 * (C) OpenEyes Foundation, 2016
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2016, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 * Class PasApiAssignment
 * @package OEModule\PASAPI\models
 *
 * @property $resource_type
 * @property $resource_id
 * @property $internal_id
 * @property $internal_type
 */
class PasApiAssignment extends \BaseActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @return PasApiAssignment the static model class
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
        return 'pasapi_assignment';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id, resource_id, resource_type, internal_id, internal_type, created_date, last_modified_date, created_user_id, last_modified_user_id', 'safe', 'on'=>'search'),
        );
    }

    /**
     * Returns the internal model that this assignment is associated with
     * (or a new instance if it has not been attached yet).
     *
     * if force_create is true, it will create a new instance if the internal id cannot find the internal model.
     * This allows for fault tolerance on internal models that might be deleted by other means.
     *
     * @param bool $force_create
     * @return \CActiveRecord
     */
    public function getInternal($force_create = false)
    {
        if ($this->internal_id) {
            $internal = self::model($this->internal_type)->findByPk($this->internal_id);
            if (!$internal && $force_create) {
                $this->internal_id = null;
                $internal = new $this->internal_type;
            }
            return $internal;
        } else {
            return new $this->internal_type;
        }

    }

    /**
     * Find or create association using resource details and lock.
     *
     * @param string $resource_type
     * @param string $resource_id
     *
     * @return PasApiAssignment
     */
    public function findByResource($resource_type, $resource_id, $internal_type = null)
    {
        $this->lock($resource_type, $resource_id);
        if (is_null($internal_type)) {
            $internal_type = 'OEModule\\PASAPI\\resources\\' . $resource_type;
        }

        $record = $this->findByAttributes(array('resource_type' => $resource_type, 'resource_id' => $resource_id));
        if (!$record) {
            $record = new static();
            $record->resource_type = $resource_type;
            $record->resource_id = $resource_id;
            // assuming all models are in the root namespace at this point
            $record->internal_type = '\\'.$internal_type;
        }

        return $record;
    }

    /**
     * Unlock the assignment.
     */
    public function unlock()
    {
        $this->dbConnection->createCommand('SELECT RELEASE_LOCK(?)')->execute(array($this->getLockKey($this->resource_type, $this->resource_id)));
    }

    /**
     * Lock the assignment so no other instances can clash with efforts to create or update the
     * record.
     *
     * @param $resource_type
     * @param $resource_id
     */
    protected function lock($resource_type, $resource_id)
    {
        $cmd = $this->dbConnection->createCommand('SELECT GET_LOCK(?, 1)');
        $key = $this->getLockKey($resource_type, $resource_id);

        while (!$cmd->queryScalar(array($key)));
    }

    protected function getLockKey($resource_type, $resource_id)
    {
        return "openeyes.mehpas.{$resource_type}:{$resource_id}";
    }
}
