<?php

namespace app\controllers;

use Yii;
use app\models\Prelands;
use app\models\PrelandsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use app\models\Buyers;
use webvimark\modules\UserManagement\models\User;

/**
 * PrelandsController implements the CRUD actions for Prelands model.
 */
class PrelandsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
			'ghost-access'=> [
				'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
			],
        ];
    }

    /**
     * Lists all Prelands models.
     * @return mixed
     */
    public function actionIndex()
    {
		$limit = $_GET['limit'] ?? '100';
		$model = Prelands::find()
			->orderBy(['id' => SORT_DESC])
			->limit($limit)
			->all();

		$otherData['allUsers'] = $this->getAllUsers($model, 'yes');

        return $this->render('index', [
            'model' => $model,
			'otherData' => $otherData,
        ]);
    }

    /**
     * Displays a single Prelands model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		$this->setUserFolder('');
		$model = $this->findModel($id);
		$otherData['allUsers'] = $this->getAllUsers($model);
        return $this->render('view', [
            'model' => $model,
			'otherData' => $otherData,
        ]);
    }
	
	public function setUserFolder($path){
		if(strlen($path) > 1){
			preg_match('/(prelands.{3,20})\//', 'prelands/'.$path, $matches);
			$_SESSION['RF']['prelandfolder'] = $matches[1];
		}else{
			$_SESSION['RF']['prelandfolder'] = '';
		}
	}

    /**
     * Creates a new Prelands model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
		$this->setUserFolder('');
        $model = new Prelands();
		$otherData['allUsers'] = $this->getAllUsers($model);
		
		if ($model->load(Yii::$app->request->post())){
			
			if($model->user_id == User::getCurrentUser()->id || User::hasRole('Admin') || User::hasRole('Developer')){
				$randomName = md5(time().'tZ9gwC$IAxS4');
				$randomFolder = rand(0, 999);
				
				if (!file_exists(Yii::$app->params['pathPrelands'].'/'.$model->user_id)) 
					mkdir(Yii::$app->params['pathPrelands'].'/'.$model->user_id, 0700);
				
				
				if (!file_exists(Yii::$app->params['pathPrelands'].'/'.$model->user_id.'/'.$randomFolder)) 
					mkdir(Yii::$app->params['pathPrelands'].'/'.$model->user_id.'/'.$randomFolder, 0700);
				
				$fd = fopen(Yii::$app->params['pathPrelands'].'/'.$model->user_id.'/'.$randomFolder.'/'.$randomName.".html", 'w') or die("не удалось создать файл");
				$str = $_POST['land_html'];
				$phpCodeInsert = '
					<script>
						//upgradeV1
						var url = window.location.search.split("offer=")[1];
						for(var allHref=document.getElementsByTagName("a"),i=0;i<allHref.length;i++){allHref[i].href=url; allHref[i].onclick="";};
					</script>
				';
				$str .= $phpCodeInsert;
				fwrite($fd, $str);
				fclose($fd);
				$model->path = $model->user_id.'/'.$randomFolder.'/'.$randomName.'.html';
				
				if ($model->save())
					return $this->redirect(['update', 'id' => $model->id]);
			}else{
				$otherData['error'] = 'У вас недостаточно прав';
			}
		}

			

        return $this->render('create', [
            'model' => $model,
			'otherData' => $otherData,
        ]);
    }

	public function getAllUsers($model, $all='no'){
		$allUsers;
		
		if((User::hasRole('Admin') || User::hasRole('Developer')) || $all='yes'){
			foreach(Buyers::find()->where([])->all() as $key => $value){
				$allUsers[$value['id']] = $value['display_name'];
			}
		}else{
			foreach(Buyers::find()->where([])->all() as $key => $value){
				if($model->user_id == $value['id'])
					$allUsers[$value['id']] = $value['display_name'];
			}
		}
		return $allUsers;
	}
    /**
     * Updates an existing Prelands model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
		$this->setUserFolder($model->path);
		$otherData['allUsers'] = $this->getAllUsers($model);
		
			$filename = Yii::$app->params['pathPrelands'].'/'.$model->path;
			$handle = fopen($filename, 'r') or die("не удалось открыть файл");
			$otherData['htmlLand'] = fread($handle, filesize($filename));
			$otherData['htmlLand'] = str_replace("<textarea>", "&lt;textarea&gt;", $otherData['htmlLand']);
			$otherData['htmlLand'] = str_replace("</textarea>", "&lt;/textarea&gt;", $otherData['htmlLand']);
			fclose($handle);
			if ($model->load(Yii::$app->request->post())) {
				if($model->user_id == User::getCurrentUser()->id || User::hasRole('Admin') || User::hasRole('Developer')){
					if($model->save()){
						$fd = fopen(Yii::$app->params['pathPrelands'].'/'.$model->path, 'w') or die("не удалось создать файл");
						if(!strripos($_POST['land_html'], 'upgradeV1')){
							$str = $_POST['land_html'];
							$phpCodeInsert = '
								<script>
									//upgradeV1
									var url = window.location.search.split("offer=")[1];
									for(var allHref=document.getElementsByTagName("a"),i=0;i<allHref.length;i++){allHref[i].href=url; allHref[i].onclick="";};
								</script>
							';
							$str .= $phpCodeInsert;
						}else{
							$str = $_POST['land_html'];
						}
						$str = preg_replace('/<\?(?:php|=|\s+).*?\?>/s','',$str);
						fwrite($fd, $str);
						fclose($fd);
						
						return $this->redirect(['update', 'id' => $model->id]);
					}
				}else{
					$otherData['error'] = 'У вас недостаточно прав';
				}
			}

        return $this->render('update', [
            'model' => $model,
			'otherData' => $otherData,
        ]);
    }

    /**
     * Deletes an existing Prelands model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
		preg_match('/(prelands.{3,20})\//', 'prelands/'.$model->path, $matches);
		$this->rmRec($matches[1]);
		$this->rmRec($matches[1].'_thumbs');
		
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
	
	function rmRec($path) {
	  if (is_file($path)) return unlink($path);
	  if (is_dir($path)) {
		foreach(scandir($path) as $p) if (($p!='.') && ($p!='..'))
			$this->rmRec($path.DIRECTORY_SEPARATOR.$p);
		return rmdir($path); 
		}
	  return false;
	  }

    /**
     * Finds the Prelands model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Prelands the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Prelands::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
