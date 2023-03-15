<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $model \app\models\UserForm
 */

use app\models\UserAccount;
use kartik\color\ColorInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cf content-wrapper">
    <h2><?= $this->title ?></h2>
    <?php \app\components\ThemeHelper::printFlashes(); ?>

    <?php $form = ActiveForm::begin([
            'options' => [
                'id' => 'settings-form',
                'data-del-image-url' => Url::toRoute('delete-image'),
                'enctype' => 'multipart/form-data'
            ]
    ]); ?>
    <div class="panel panel-default">
        <div class="panel-heading"><?= Yii::t('app', 'Main format') ?></div>
        <div class="panel-body">
            <div class="col-sm-12">
                <div class="form-group">
                    <?= Html::label(Yii::t('app', 'Avatar'), null, ['class' => 'control-label']) ?>
                    <?= Html::fileInput('avatar_array[]', null, ['multiple' => true, 'accept' => '.png, .jpg, .jpeg']) ?>
                </div>
            </div>
            <div class="col-sm-12 img-section-wrapper">
                <?php foreach($model->style_template->avatar_array as $image): ?>
                    <label class="img-thumbnail-wrapper">
                        <?= Html::img("data:image/gif;base64, {$image['logo_image_body']}", [
                            'class' => 'img-thumbnail',
                            'style' => ['max-height' => '100px']
                        ]) ?>
                        <?= Html::radio("{$model->style_template->formName()}[avatar]", $model->style_template->avatar == $image['pk'], ['value' => $image['pk']]) ?>
                        <button type="button" class="close js-delete-image" data-model-attr="avatar" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </label>
                <?php endforeach ?>
            </div>
        </div>
    </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Body') ?></div>
            <div class="panel-body">
                <div class="col-sm-12 style-switcher">
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-primary js-style-switcher <?php if(!$model->style_template->use_body_images): ?>active<?php endif; ?>">
                            <?= Html::radio("{$model->style_template->formName()}[use_body_images]", $model->style_template->use_body_images, ['value' => false]) ?>
                            <?= Yii::t('app', 'Use styles') ?>
                        </label>
                        <label class="btn btn-primary js-style-switcher <?php if($model->style_template->use_body_images): ?>active<?php endif; ?>">
                            <?= Html::radio("{$model->style_template->formName()}[use_body_images]", $model->style_template->use_body_images, ['value' => true]) ?>
                            <?= Yii::t('app', 'Use images') ?>
                        </label>
                    </div>
                </div>
                <div class="style-case-wrapper" <?php if($model->style_template->use_body_images): ?> style="display:none" <?php endif; ?>>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'border_size')->dropDownList(UserAccount::getBorderSizeAllowed(), [
                            'class' => 'form-control',
                            'onchange' => "
                                $('.tab-pane, .panel').css('cssText', 'border: ' + $(this).val() + ' solid ' + $('#userstyletemplateform-border_color').val() + ' !important');
                            "
                        ]); ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'border_color')->widget(ColorInput::classname(), [
                            'pluginEvents' => [
                                'change' => "function(e, color) {
//                                    $('.tab-pane, .panel').css('cssText', 'border: ' + $('#userstyletemplateform-border_size').val() + ' solid ' + color.toHexString() + ' !important');
//                                    $('.navbar').css('cssText', 'border-color: ' + color.toHexString() + ' !important');
                                    $('.tab-pane, .panel').css('cssText', 'border: ' + $('#userstyletemplateform-border_size').val() + ' solid ' + color + ' !important');
                                    $('.navbar').css('cssText', 'border-color: ' + color + ' !important');
                                }"
                            ]
                        ]); ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'background_color')->widget(ColorInput::classname(), [
                            'pluginEvents' => [
                                'change' => "function(e, color) { 
                                    $('body').css('cssText', 
                                        'color: ' + $('#userstyletemplateform-text_color').val() + ' !important;' + 
//                                        'background-color: ' + color.toHexString() + ' !important;'
                                        'background-color: ' + color + ' !important;'
                                    );
                                }"
                            ]
                        ]); ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'link_color')->widget(ColorInput::classname()); ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'text_color')->widget(ColorInput::classname(), [
                            'pluginEvents' => [
                                'change' => "function(e, color) { 
                                    $('body').css('cssText', 
//                                        'color: ' + color.toHexString() + ' !important;' + 
                                        'color: ' + color + ' !important;' + 
                                        'background-color: ' + $('#userstyletemplateform-background_color').val() + ' !important'
                                    );
                                }"
                            ]
                        ]); ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'info_color')->widget(ColorInput::classname(), [
                            'pluginEvents' => [
                                'change' => "function(e, color) {
//                                    $('label, h3').css('cssText', 'color: ' + color.toHexString() + ' !important;');
                                    $('label, h3').css('cssText', 'color: ' + color + ' !important;');
                                }"
                            ]
                        ]); ?>
                    </div>
                </div>
                <div class="image-case-wrapper" <?php if(!$model->style_template->use_body_images): ?> style="display:none" <?php endif; ?>>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?= Html::label(Yii::t('app', 'Background image'), null, ['class' => 'control-label']) ?>
                            <?= Html::fileInput('background_image_array[]', null, ['multiple' => true, 'accept' => '.png, .jpg, .jpeg']) ?>
                        </div>
                    </div>
                    <div class="col-sm-12 img-section-wrapper">
                    <?php foreach($model->style_template->background_image_array as $image): ?>
                        <label class="img-thumbnail-wrapper">
                            <?= Html::img("data:image/gif;base64, {$image['logo_image_body']}", [
                                'class' => 'img-thumbnail',
                                'style' => ['max-height' => '100px']
                            ]) ?>
                            <?= Html::radio("{$model->style_template->formName()}[background_image]", $model->style_template->background_image == $image['pk'], ['value' => $image['pk']]) ?>
                            <button type="button" class="close js-delete-image" data-model-attr="background_image" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </label>
                    <?php endforeach ?>
                </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Menu') ?></div>
            <div class="panel-body">
                <div class="col-sm-12 style-switcher">
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-primary js-style-switcher <?php if(!$model->style_template->use_menu_images): ?>active<?php endif; ?>">
                            <?= Html::radio("{$model->style_template->formName()}[use_menu_images]", !$model->style_template->use_menu_images, ['value' => false]) ?>
                            <?= Yii::t('app', 'Use styles') ?>
                        </label>
                        <label class="btn btn-primary js-style-switcher <?php if($model->style_template->use_menu_images): ?>active<?php endif; ?>">
                            <?= Html::radio("{$model->style_template->formName()}[use_menu_images]", $model->style_template->use_menu_images, ['value' => true]) ?>
                            <?= Yii::t('app', 'Use images') ?>
                        </label>
                    </div>
                </div>
                <div class="style-case-wrapper" <?php if($model->style_template->use_menu_images): ?> style="display:none" <?php endif; ?>>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'menu_background')->widget(ColorInput::classname(), [
                            'pluginEvents' => [
                                'change' => "function(e, color) {
//                                    $('.navbar:not(.info-place):not(.error-message),.navbar:not(.info-place):not(.error-message) .dropdown-menu, .left-position-navbar').css('cssText', 'background-color: ' + color.toHexString() + ' !important;');
                                    $('.navbar:not(.info-place):not(.error-message),.navbar:not(.info-place):not(.error-message) .dropdown-menu, .left-position-navbar').css('cssText', 'background-color: ' + color + ' !important;');
                                }"
                            ]
                        ]); ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($model->style_template, 'menu_text_color')->widget(ColorInput::classname(), [
                            'pluginEvents' => [
                                'change' => "function(e, color) {
//                                    $('.navbar-default .navbar-nav > li a, .dropdown.nav-features a, .left-position-navbar-menu a, .left-position-navbar .feature-block a').css('cssText', 'color: ' + color.toHexString() + ' !important;');
                                    $('.navbar-default .navbar-nav > li a, .dropdown.nav-features a, .left-position-navbar-menu a, .left-position-navbar .feature-block a').css('cssText', 'color: ' + color + ' !important;');
                                }"
                            ]
                        ]); ?>
                    </div>
                    <div class="col-sm-4">
                    <?= $form->field($model->style_template, 'highlight_color_selection')->widget(ColorInput::classname()); ?>
                    </div>
                </div>
                <div class="image-case-wrapper" <?php if(!$model->style_template->use_menu_images): ?> style="display:none" <?php endif; ?>>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?= Html::label(Yii::t('app', 'Menu background image'), null, ['class' => 'control-label']) ?>
                            <?= Html::fileInput('menu_background_image_array[]', null, ['multiple' => true, 'accept' => '.png, .jpg, .jpeg']) ?>
                        </div>
                    </div>
                    <div class="col-sm-12 img-section-wrapper">
                        <?php foreach($model->style_template->menu_background_image_array as $image): ?>
                            <label class="img-thumbnail-wrapper">
                                <?= Html::img("data:image/gif;base64, {$image['logo_image_body']}", [
                                    'class' => 'img-thumbnail',
                                    'style' => ['max-height' => '100px']
                                ]) ?>
                                <?= Html::radio("{$model->style_template->formName()}[menu_background_image]", $model->style_template->menu_background_image == $image['pk'], ['value' => $image['pk']]) ?>
                                <button type="button" class="close js-delete-image" data-model-attr="menu_background_image" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </label>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Section') ?></div>
            <div class="panel-body">
				<div class="col-sm-4">
					<?= $form->field($model->style_template, 'section_border_size')->dropDownList(UserAccount::getBorderSizeAllowed()); ?>
				</div>
                <div class="col-sm-4">
                    <?= $form->field($model->style_template, 'section_header_color')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->style_template, 'section_header_background')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->style_template, 'section_background_color')->widget(ColorInput::classname()); ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Header') ?></div>
            <div class="panel-body">
                <div class="col-sm-4">
                    <?= $form->field($model->style_template, 'header_border_size')->dropDownList(UserAccount::getBorderSizeAllowed()); ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->style_template, 'header_color')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->style_template, 'header_border_color')->widget(ColorInput::classname()); ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Search field') ?></div>
            <div class="panel-body">
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'search_border_color')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'search_border_selected_color')->widget(ColorInput::classname()); ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Fields') ?></div>
            <div class="panel-body">
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'field_border_color')->widget(ColorInput::classname(), [
                        'pluginEvents' => [
                            'change' => "function(e, color) {
//                                $('.input-group-addon').css('cssText', 'border-color: ' + color.toHexString() + ' !important; width: 60px;');
//                                $('.spectrum-input, select').css('cssText', 'border-color: ' + color.toHexString() + ' !important;');
                                $('.input-group-addon').css('cssText', 'border-color: ' + color + ' !important; width: 60px;');
                                $('.spectrum-input, select').css('cssText', 'border-color: ' + color + ' !important;');
                            }"
                        ]
                    ]); ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'field_border_selected_color')->widget(ColorInput::classname()); ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Tab') ?></div>
            <div class="panel-body">
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'tab_selected_color')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'tab_unselected_color')->widget(ColorInput::classname()); ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Message line') ?></div>
            <div class="panel-body">
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'message_line_color')->widget(ColorInput::classname(), [
                        'pluginEvents' => [
                            'change' => "function(e, color) {
//                                $('.info-place').css('cssText', 'color: ' + color.toHexString() + ' !important; background-color: ' + $('#userstyletemplateform-message_line_background').val() + ' !important');
                                $('.info-place').css('cssText', 'color: ' + color + ' !important; background-color: ' + $('#userstyletemplateform-message_line_background').val() + ' !important');
                            }"
                        ]
                    ]); ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'message_line_background')->widget(ColorInput::classname(), [
                        'pluginEvents' => [
                            'change' => "function(e, color) {
//                                $('div:not(.nav-left-group) > .info-place, div:not(.sub-content-wrapper) > div > .nav-left-group').css('cssText', 'background-color: ' + color.toHexString() + ' !important; color: ' + $('#userstyletemplateform-message_line_color').val() + ' !important');
                                $('div:not(.nav-left-group) > .info-place, div:not(.sub-content-wrapper) > div > .nav-left-group').css('cssText', 'background-color: ' + color + ' !important; color: ' + $('#userstyletemplateform-message_line_color').val() + ' !important');
                            }"
                        ]
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Chart color') ?></div>
            <div class="panel-body">
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'chart_color_first')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'chart_color_second')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'chart_color_third')->widget(ColorInput::classname()); ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->style_template, 'chart_color_fourth')->widget(ColorInput::classname()); ?>
                </div>
            </div>
        </div>

        <div class="text-right">
            <?= Html::button(Yii::t('app', 'Default settings'), [
                'class' => 'btn btn-warning',
                'onclick' => "setDefault()",
                'title' => 'Be careful. This button fills in the default values, but does not save them.'
            ]) ?>
            <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJs("
    function setDefault() {
        console.log('function setDefault()');
        
        var defaultStyles = " . json_encode($default) . ";
        var settingsForm = $('#settings-form');
                var inputs = settingsForm.find($('[id^=\"userstyletemplateform\"]'));
                
        $.each(inputs, function(key, value) {
           var elem = $(value);
           var elemId = elem.attr('id');
           var fieldName = elemId.split('-');
           
           if (!$.isEmptyObject(fieldName[1])) {
               if (!$.isEmptyObject(fieldName[2]) && fieldName[2] == 'source') {
                   if (!$.isEmptyObject(defaultStyles[fieldName[1]])) {
                       $('#' + elemId).spectrum('set', defaultStyles[fieldName[1]]).trigger('change', defaultStyles[fieldName[1]]);
                   } else {
                       $('#' + elemId).spectrum('set', '').trigger('change', '');
                   }
               } else {
                   if (!$.isEmptyObject(defaultStyles[fieldName[1]])) {
                       $('#' + elemId).val(defaultStyles[fieldName[1]]).trigger('change', defaultStyles[fieldName[1]]);
                   } else {
                       $('#' + elemId).val('').trigger('change', '');
                   }
               }
               
           }
         })
         
        if (confirm('Do you want to keep the default styles?')) {
            $('#settings-form').trigger('submit');
        }
/*
        $('#userform-border_size').val('" . $default['border_size'] . "').trigger('change');
        $('#userform-background_color').val('" . $default['background_color'] . "').trigger('change');
        $('#userform-border_color').val('" . $default['border_color'] . "').trigger('change');
        $('#userform-header_border_size').val('" . $default['header_border_size'] . "').trigger('change');
        
        $('#userform-header_border_color').val('" . $default['border_color'] . "').trigger('change');
        $('#userform-tab_selected_color').val('" . $default['tab_selected_color'] . "').trigger('change');
        $('#userform-section_background_color').val('" . $default['section_background_color'] . "').trigger('change');
        
        $('#userform-text_color').val('" . $default['text_color'] . "').trigger('change');
        $('#userform-link_color').val('" . $default['link_color'] . "').trigger('change');
        $('#userform-info_color').val('" . $default['info_color'] . "').trigger('change');
        $('#userform-header_color').val('" . $default['header_color'] . "').trigger('change');
        
        $('#userform-search_border_color').val('" . $default['border_color'] . "').trigger('change');
        $('#userform-tab_unselected_color').val('" . $default['tab_unselected_color'] . "').trigger('change');
        $('#userform-highlight_color_selection').val('" . $default['highlight_color_selection'] . "').trigger('change');
        
        $('#userform-highlight_color_selection').val('" . $default['highlight_color_selection'] . "').trigger('change');
        
        $('#userform-menu_background').val('" . $default['menu_background'] . "').trigger('change');
        $('#userform-message_line_color').val('" . $default['message_line_color'] . "').trigger('change');
        $('#userform-section_header_color').val('" . $default['section_header_color'] . "').trigger('change');
        
        $('#userform-message_line_background').val('" . $default['message_line_background'] . "').trigger('change');
        $('#userform-field_border_color').val('" . $default['field_border_color'] . "').trigger('change');
        $('#userform-field_border_selected_color').val('" . $default['field_border_selected_color'] . "').trigger('change');
        $('#userform-search_border_selected_color').val('" . $default['search_border_selected_color'] . "').trigger('change');
        $('#userform-section_header_background').val('" . $default['section_header_background'] . "').trigger('change');
        $('#userform-menu_text_color').val('" . $default['menu_text_color'] . "').trigger('change');
        
        $('#userform-chart_color_first').val('" . $default['chart_color_first'] . "').trigger('change');
        $('#userform-chart_color_second').val('" . $default['chart_color_second'] . "').trigger('change');
        $('#userform-chart_color_third').val('" . $default['chart_color_third'] . "').trigger('change');
        $('#userform-chart_color_fourth').val('" . $default['chart_color_fourth'] . "').trigger('change');
        
        $('#settings-form').trigger('submit');
*/
    };
", \yii\web\View::POS_HEAD);
?>
