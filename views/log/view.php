<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Log */

$this->title = $model->id .' at '. $model->at_datetime;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<div class="container-fluid">

    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-inbox bg-blue"></i>
                    <div class="d-inline">
                        <h5><?=$this->title;?></h5>
                        <span><?=$this->title;?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav class="breadcrumb-container" aria-label="breadcrumb">
                    <?=\yii\widgets\Breadcrumbs::widget([
                        'tag'=>'ol',
                        'activeItemTemplate' => '<li class="breadcrumb-item active">{link}</li>',
                        'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],

                    ]) ?>
                </nav>
            </div>
        </div>
    </div>


    <div class="log-view card p20">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'at_datetime',
            'ip',
            'ipv6',
            'ua',
            'referer',
            'referer_prelanding',
            'manager_key',
            'language',
            'country',
            'city',
            'isp',
            'asn',
            'os',
            'browser',
            'external_uclick',
            'log_type',
            [
                'attribute'=>'detailed',
                'format'=>'html',
                'value'=>function($data){
                    return sprintf('<div class="bobob"><pre>%s</pre></div>', $data->detailed);
                }
            ],
            'is_bot',
            [
                'attribute'=>'meta_data',
                'format'=>'html',
                'value'=>function($data){
                    return sprintf('<div class="jsonfy"><pre>%s</pre></div>', $data->meta_data);
                }
            ],
        ],
    ]) ?>

</div>

<?
$this->registerCss('pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }.string { color: green; }.number { color: darkorange; }.boolean { color: blue; }.null { color: magenta; }.key { color: red; }');
$this->registerJs('$(function () {
        var jj = $("div.jsonfy>pre").html();
        var jjfy = JSON.stringify(JSON.parse(jj), undefined, 4);
        $("div.jsonfy>pre").html(syntaxHighlight(jjfy));
    });');
?>

<script>
    /*$(function () {
        var jj = $("div.jsonfy>pre").html();
        var jjfy = JSON.stringify(JSON.parse(jj), undefined, 4);
        $("div.jsonfy>pre").html(syntaxHighlight(jjfy));
    });*/
    function syntaxHighlight(json) {
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }
</script>

</div>
