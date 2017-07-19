<?php
/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2017
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2017, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

?>

<?php
if (!isset($values)) {
    $values = array(
        'id' => $op->id,
        'operation' => $op->operation,
        'side_id' => $op->side_id,
        'side_display' => $op->side ? $op->side->adjective : 'None',
        'date' => $op->date,
        'date_display' => $op->getDisplayDate()
    );
}

if (isset($values['date']) && strtotime($values['date'])) {
    list($sel_year, $sel_month, $sel_day) = explode('-', $values['date']);
} else {
    $sel_day = $sel_month = null;
    $sel_year = date('Y');
}

?>
<tr class="row-<?=$row_count;?>" data-row_number="<?=$row_count;?>">
    <td>
        <input type="hidden" name="<?= $field_prefix ?>[id]" value="<?=$values['id'] ?>" />

        <?php echo CHtml::dropDownList(null, '',
            CHtml::listData(CommonPreviousOperation::model()->findAll(
                array('order' => 'display_order asc')), 'id', 'name'),
            array('empty' => '- Select -', 'class' => $model_name . '_operations'))?>
        <br><br>
        <?php echo CHtml::textField($field_prefix . '[operation]', $values['operation'], array(
            'placeholder' => 'Common Operation',
            'autocomplete' => Yii::app()->params['html_autocomplete'],
            'class' => 'common-operation',
            )); ?>
    </td>
    <td class="<?= $model_name ?>_sides" style="white-space:nowrap">

        <input type="hidden" name="<?=$field_prefix?>[side_id]" value="<?=$values['side_id']; ?> " />

        <label class="inline">
            <input type="radio"
                   name="<?="side_group_name_$row_count"; ?>"
                   class="<?= $model_name ?>_previous_operation_side"
                   <?php if(empty($values['side_id'])): ?> checked <?php endif; ?>
                   value="" /> None
        </label>
        <?php foreach (Eye::model()->findAll(array('order' => 'display_order')) as $i => $eye) {?>
            <label class="inline">
                <input
                    type="radio" name="<?="side_group_name_$row_count"; ?>"
                    class="<?= $model_name ?>_previous_operation_side"
                    value="<?php echo $eye->id?>"
                    <?php if($eye->id == $values['side_id']){ echo "checked"; }?>
                />
                <?php echo $eye->name ?>
            </label>
        <?php }?>
    </td>
    <td>
        <input type="hidden" name="<?= $field_prefix ?>[date]" value="<?=$values['date'] ?>" />

        <fieldset id="<?= $model_name ?>_fuzzy_date" class="row field-row fuzzy_date" style="padding:0">
            <div class="large-12 column end">
                <div class="row">
                    <div class="large-3 column">
                        <select class="fuzzy_day">
                            <option value="0">- Day -</option>
                            <?php for ($i = 1;$i <= 31;++$i) {?>
                                <option value="<?= $i?>"<?= ($i == $sel_day) ? ' selected' : ''?>><?= $i?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="large-4 column">
                        <select class="fuzzy_month">
                            <option value="0">- Month- </option>
                            <?php foreach (array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') as $i => $month) {?>
                                <option value="<?= $i + 1?>"<?= ($i + 1 == $sel_month) ? ' selected' : ''?>><?= $month?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="large-3 column">
                        <select class="fuzzy_year">
                            <option value="0">- Year -</option>
                            <?php for ($i = date('Y') - 50;$i <= date('Y');++$i) {?>
                                <option value="<?= $i?>"<?= ($i == $sel_year) ? ' selected' : ''?>><?= $i?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="large-1 column end">
                        <span class="has-tooltip fa fa-info-circle right" style="margin-top:3px"
                              data-tooltip-content="Day, Month and Year fields are optional."></span>
                    </div>
                </div>
            </div>
        </fieldset>
    </td>

    <?php if($removable) : ?>

    <td class="edit-column">
        <button class="button small warning remove">remove</button>
    </td>
    <?php else: ?>
    <td>read only <span class="has-tooltip fa fa-info-circle" data-tooltip-content="This operation is recorded as an Operation Note event in OpenEyes and cannot be edited here"></span></td>
    <?php endif; ?>

</tr>