<?php

namespace app\controllers;

use Yii;
use app\models\CostingApproval;
use app\models\CostingApprovalSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

use app\models\Project;
use app\models\ProjectSearch;
use yii\filters\AccessControl;

/**
 * CostingApprovalController implements the CRUD actions for CostingApproval model.
 */
class CostingApprovalController extends Controller
{
    private $accessid = "CREATE-CAPPROVAL";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        //'actions' => ['login', 'error'], // Define specific actions
                        'allow' => true, // Has access
                        'matchCallback' => function ($rule, $action) {
                            return \app\models\User::getIsAccessMenu($this->accessid);
                        }
                    ],
                    [
                        'allow' => false, // Do not have access
                        'roles'=>['?'], // Guests '?'
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all CostingApproval models.
     * @return mixed
     */
    public function actionIndex($projectid = 0)
    {
        if($projectid == 0){
            $searchModel = new ProjectSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('project-index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
        else {
            $searchModel = new CostingApprovalSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $projectid);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);    
        }
        
    }

    /**
     * Displays a single CostingApproval model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id, $projectid)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CostingApproval model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($projectid = 0)
    {
        $model = new CostingApproval();
        $model->projectid = $projectid;

        //initial user change & date
        $model->userin = Yii::$app->user->identity->username;
        $model->datein = new \yii\db\Expression('NOW()');

        if ($model->load(Yii::$app->request->post())) {
            $file1 = UploadedFile::getInstance($model, 'file');
            
            date_default_timezone_set('Asia/Jakarta');

            $model->date =  new \yii\db\Expression('NOW()');            
            $model->filename = str_replace('/', '.', $model->project->code).'_'.date('d.M.Y').'_'.date('His').'_'.'CostingApproval'. '.' . $file1->extension;
            $model->filename = strtoupper($model->filename);
            $model->file = $file1;
            
            if ($model->validate() && $model->save()) {

                $model_project = new Project();
                $model_project = Project::findOne($projectid);                
                $model_project->setProjectStatus();

                $model->file->saveAs('uploads/' . $model->filename); 
                return $this->redirect(['view', 'id' => $model->costingapprovalid, 'projectid'=>$projectid]);
            }
            else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
        else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }

        /*
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->costingapprovalid]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
        */
    }

    /**
     * Updates an existing CostingApproval model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id, $projectid)
    {
        $model = $this->findModel($id);

        //initial user change & date
        $model->userup = Yii::$app->user->identity->username;
        $model->dateup = new \yii\db\Expression('NOW()');

        if ($model->load(Yii::$app->request->post())) {

            $file1 = UploadedFile::getInstance($model, 'file');
            
            date_default_timezone_set('Asia/Jakarta');

            $model->date =  new \yii\db\Expression('NOW()');
            $model->filename = str_replace('/', '.', $model->project->code).'_'.date('d.M.Y').'_'.date('His').'_'.'CostingApproval'. '.' . $file1->extension;
            $model->filename = strtoupper($model->filename);
            $model->file = $file1;
            
            if ($model->validate() && $model->save()) {                
                $model->file->saveAs('uploads/' . $model->filename); 

                $model_project = new Project();
                $model_project = Project::findOne($projectid);                
                $model_project->setProjectStatus();

                return $this->redirect(['view', 'id' => $model->costingapprovalid, 'projectid'=>$projectid]);
            }
            else{
                return $this->render('update', [
                    'model' => $model,
                ]);    
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        /*
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->costingapprovalid]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
        */
    }

    /**
     * Deletes an existing CostingApproval model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $projectid = $model->projectid;
        $model->delete();

        $model_project = new Project();
        $model_project = Project::findOne($projectid);                
        $model_project->setProjectStatus();

        return $this->redirect(['index', 'projectid'=>$projectid]);
    }

    /**
     * Finds the CostingApproval model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CostingApproval the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CostingApproval::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
