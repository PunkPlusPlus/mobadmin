<?php

use yii\grid\GridView;
use yii\widgets\ListView;

?>

<div style="width: 60%">

    <?php

    //    echo ListView::widget([
    //        'dataProvider' => $dataProvider,
    //        'itemView' => '_user',
    //    ]);

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            // Обычные поля определенные данными содержащимися в $dataProvider.
            // Будут использованы данные из полей модели.
            'id',
            'display_name',
            // Более сложный пример.
            [
                'class' => 'yii\grid\DataColumn', // может быть опущено, поскольку является значением по умолчанию
                'label' => 'Token',
                'value' => function ($data) {
                    $token = \app\models\Tokens::find()->where(['user_id' => $data->id])->one();
                    if ($token) return $token->token;
                    return "-";
                },
            ],
            [
                'label' => 'Options',
                'value' => function ($data) {
                    $token = \app\models\Tokens::find()->where(['user_id' => $data->id])->one();
                    $editButton = "<a href=\"/apiv1/access/edit?id=$data->id\" class='btn btn-primary'>Refresh token</a>";
                    $createButton = "<a href=\"/apiv1/access/create?id=$data->id\" class='btn btn-primary'>Create token</a>";

                    if ($token) {
                        return $editButton;
                    } else {
                        return $createButton;
                    }
                },
                'format' => 'raw'
            ],
            [
                'label' => 'Delete tokens',
                'value' => function ($data) {
                    $token = \app\models\Tokens::find()->where(['user_id' => $data->id])->one();
                    if ($token) {
                        return "<a href=\"/apiv1/access/delete?id=$data->id\" class='btn btn-primary'>Delete token</a>";
                    } else {
                        return "<button class='btn btn-primary' disabled>Delete token</button>";
                    }

                },
                'format' => 'raw'
            ]
        ],
    ]);

    ?>

</div>

