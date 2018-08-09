<?php
/**
 * OpenEyes.
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2011-2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */
?>
<div class="oe-full-header flex-layout">
    <?php
    $qs_svc = Yii::app()->service->getService($this::$QUEUESET_SERVICE);
    ?>
    <div class="title wordcaps">
        <b><?= $queueset ? $queueset->name : $category->name ?></b>
    </div>

    <?php
    if ($queueset) {
        if ($flash_message = Yii::app()->user->getFlash('patient-ticketing-' . $queueset->getId())) {
            ?>
            <br/>
            <div class="large-12 column">
                <div class="panel">
                    <div class="alert-box with-icon success">
                        <?php echo $flash_message;
                        ?>
                    </div>
                </div>
            </div>
            <?php

        }
    }

    ?>
    <div <?php if (!$queueset) { ?> style="display: none;"<?php } ?>>
        <button class="button blue hint" id="js-virtual-clinic-btn">Change <?= $category->name ?></button>
    </div>
</div>
<div class="oe-full-content oe-virtual-clinic flex-layout flex-top">
    <?php
    if ($queueset) {
        $this->renderPartial('ticketlist', array(
            'qs_svc' => $qs_svc,
            'category' => $category,
            'queueset' => $queueset,
            'tickets' => $tickets,
            'patient_filter' => $patient_filter,
            'pages' => $pages,
            'cat_id' => $cat_id,
        ));
    }
    ?>
</div>
<?= $this->renderPartial('form_queueset_select', array(
    'category' => $category,
    'queueset' => $queueset,
    'cat_id' => $cat_id,
));
?>
