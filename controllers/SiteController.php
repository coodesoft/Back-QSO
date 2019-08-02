<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use app\models\LoginForm;
use app\models\Player;

use yii2tech\csvgrid\CsvGrid;
use yii\data\ActiveDataProvider;

class SiteController extends Controller{
    
    
    private $privatePass = 'c4prich0##r4bi050';

    /**
     * {@inheritdoc}
     */
    public function behaviors(){
        return [
            'corsFilter'  => [
                'class' => \yii\filters\Cors::className(),
                'cors'  => [
                    //restrict access to domains:
                   'Origin'                           => ['*'],
                   'Access-Control-Request-Method'    => ['POST'],
                   'Access-Control-Allow-Credentials' => false,
                   'Access-Control-Max-Age'           => 3600,          
                   'Access-Control-Allow-Headers'     => ['content-type'],
                   //'Access-Control-Expose-Headers'    => [],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout', 'export'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(){
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex(){
        return $this->actionLogin();
    }


    public function actionLogin(){
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/players']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['site/players']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }
    
    public function actionPlayers(){
        if (Yii::$app->user->isGuest) 
            return $this->goHome();
        
        return $this->render('index');
    }
    
    public function actionAdd(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (Yii::$app->request->isPost){
            
            $token = Yii::$app->request->get('token');
            $timestamp = Yii::$app->request->get('t');
            
            $hash = md5($this->privatePass . $timestamp);
            if ($hash !== $token){
                return [
                    'status' => 'failure',
                    'message' => 'Se produjo un error de autenticación. El token es inválido',
                ];
            }
            
            $params = Json::decode(Yii::$app->request->rawBody);
            
            
            
            
            $name = $params['NombreApellido'];
            $company = $params['Compania'];
            $position = $params['Puesto'];
            $mail = $params['Mail'];
            
            if ($name && $company && $position && $mail){
                $player = new Player();
                $player->fullname = $name;
                $player->company = $company;
                $player->position = $position;
                $player->mail = $mail;
                
                if (!$player->save()) {
                    return [
                        'status' => 'error',
                        'message' => 'Se produjo un error al guardar el jugador',
                    ];
                } else{
                    return [
                        'status' => 'success',
                        'message' => 'Se guardó exitosamente el jugador',
                    ];
                    
                }
            }
            
        } else
            return [
                    'status' => 'failure',
                    'message' => 'Se produjo un error de petición. El verbo es inválido',
                ];
    }

    public function actionLogout(){
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionExport(){
        $exporter = new CsvGrid([
            'dataProvider' => new ActiveDataProvider([
                'query' => Player::find(),
            ]),
        ]);
        return $exporter->export()->send('jugadores.csv');
    }
}
