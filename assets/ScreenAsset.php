<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\assets;

use app\models\UserAccount;
use Exception;
use Throwable;
use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\View;

class ScreenAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/app.js',
        'js/jquery.mask-money.min.js',
        'js/lodash.js',
        'js/gridstack.min.js',
        'js/bootbox.min.js',
        'js/jquery.dataTables.min.js',
        'js/dataTables.bootstrap.min.js',
		'js/dataTables.buttons.min.js',
		'js/jszip.min.js',
		'js/pdfmake.min.js',
		'js/vfs_fonts.js',
		'js/buttons.html5.min.js',
        'js/moment.js',
		'js/script.js',
		'js/chartjs-plugin-regression-master/dist/chartjs-plugin-regression-0.2.1.js'
    ];

    public $css = [
        'css/gridstack.css',
        'css/dataTables.bootstrap.min.css',
		'css/buttons.dataTables.min.css',
		'css/progress_bar_style.css'
    ];

    public $depends = [
        AppAsset::class
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

    /**
     * Registers the CSS and JS files with the given view.
     *
     * @param View $view the view that the asset files are to be registered with.
     *
     * @throws Exception
     * @throws Throwable
     */
    public function registerAssetFiles($view)
    {
        $view->registerJs("
            common.setBaseUrl('" . Url::toRoute(['/site/render-tab']) . "');
            common.setLockRecordUrl('" . Url::toRoute(['/site/lock-data']) . "');
            common.setUnlockRecordUrl('" . Url::toRoute(['/site/unlock-data']) . "');
            common.setSubDataUrl('" . Url::toRoute(['/site/get-sub-data']) . "');
            common.setLoadUrl('" . Url::toRoute(['/site/get-load-data']) . "');
            common.setSearchUrl('" . Url::toRoute(['/site/search-data']) . "');
            common.setInlineSearchUrl('" . Url::toRoute(['/site/search-inline-data']) . "');
            common.setDownloadInitUrl('" . Url::toRoute(['/file/init-download']) . "');
            common.setDownloadFragmentUrl('" . Url::toRoute(['/file/download-fragment']) . "');
            common.setDownloadFinishUrl('" . Url::toRoute(['/file/finish-download']) . "');
            common.setUploadInitUrl('" . Url::toRoute(['/file/init-upload']) . "');
            common.setUploadFragmentUrl('" . Url::toRoute(['/file/upload-fragment']) . "');
            common.setUploadFinishUrl('" . Url::toRoute(['/file/finish-upload']) . "');
            common.setCustomExecuteUrl('" . Url::toRoute(['/site/custom-execute']) . "');
            common.setWorkflowReleaseUrl('" . Url::toRoute(['/workflow/release']) . "');
            common.setWorkflowLockUrl('" . Url::toRoute(['/workflow/lock']) . "');
            common.setWorkflowUnlockUrl('" . Url::toRoute(['/workflow/unlock']) . "');
            common.setGenerateReportUrl('" . Url::toRoute(['/report/generate']) . "');
            common.setSearchReportUrl('" . Url::toRoute(['/report/search']) . "');
            common.setSaveWorkflowTaskUrl('" . Url::toRoute(['/workflow/update-task']) . "');
            common.setGetWorkflowTaskUrl('" . Url::toRoute(['/workflow/get-task']) . "');
            common.setGetWorkflowStepUrl('" . Url::toRoute(['/workflow/get-step']) . "');
            common.setCreateWorkflowTaskUrl('" . Url::toRoute(['/workflow/create-task']) . "');
			common.setWorkflowJsonUrl('" . Url::toRoute(['/workflow/get-workflow-json']) . "');
            common.setGetUserListUrl('" . Url::toRoute(['/site/get-user-list']) . "');
            common.setGetScreenLinkUrl('" . Url::toRoute(['/workflow/get-screen-url']) . "');
            common.setGetTaskHistoryUrl('" . Url::toRoute(['/workflow/get-task-history']) . "');
            common.setGetDocumentListUrl('" . Url::toRoute(['/file/get-document-list']) . "');
            common.setGetAnnotatePdfUrl('" . Url::toRoute(['/file/annotate-pdf']) . "');
            common.setDocumentUploadUrl('" . Url::toRoute(['/file/document-upload']) . "');
            common.setDocumentInitUploadUrl('" . Url::toRoute(['/file/document-init-upload']) . "');
            common.setDocumentUploadFragmentUrl('" . Url::toRoute(['/file/document-upload-fragment']) . "');
            common.setDocumentFinishUploadUrl('" . Url::toRoute(['/file/document-finish-upload']) . "');
            common.setDeleteDocumentUrl('" . Url::toRoute(['/file/document-delete']) . "');
            common.setGetDeletedDocumentListUrl('" . Url::toRoute(['/file/get-deleted-document-list']) . "');
            common.setUndeletedDocumentUrl('" . Url::toRoute(['/file/document-undelete']) . "');
            common.setUpdateDocumentUrl('" . Url::toRoute(['/file/document-update']) . "');
            common.setDocumentDownloadFragmentUrl('" . Url::toRoute(['/file/document-download-fragment']) . "');
            common.setDocumentDownloadUrl('" . Url::toRoute(['/file/document-download']) . "');
            common.setGetFieldUploadImageUrl('" . Url::toRoute(['/file/field-upload-image']) . "');
            common.setinlineSearchTempUrl('" . Url::toRoute(['/site/search-inline-temp-data']) . "');
            common.setsearchLinkedListCustomQueryUrl('" . Url::toRoute(['/site/search-linked-list-custom-query']) . "');
            common.setExportTableDataUrl('" . Url::toRoute(['/site/export-table-data']) . "');
            common.setCheckLoginUrl('" . Url::toRoute(['/site/check-login']) . "');
            common.setCheckEmailLoginUrl('" . Url::toRoute(['/site/check-email']) . "');
            common.setCheckSMSLoginUrl('" . Url::toRoute(['/site/check-sms']) . "');
			common.setCheckSQLoginUrl('" . Url::toRoute(['/site/check-secret-question']) . "');
			common.setResetPasswordUrl('" . Url::toRoute(['/site/reset-password']) . "');
			common.setRegistrationUrl('" . Url::toRoute(['/site/registration']) . "');
        ", View::POS_HEAD);

        $settings = UserAccount::getSettings();
        if (!empty($settings->currencyformat_code)) {
            preg_match('/#+([^#])#+([^#])#+/', $settings->currencyformat_code, $matches);
            $view->registerJs('common.setCurrencyProperty("' . $matches[1] . '", "' . $matches[2] . '");');
            $view->registerJs('common.setDecimalProperty("' . $matches[1] . '", "' . $matches[2] . '");');
        }

        return parent::registerAssetFiles($view);
    }
}
