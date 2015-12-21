<?php
namespace backend\controllers;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use common\models\LoginForm;
use backend\models\SignupForm;
use yii\filters\VerbFilter;
use backend\base\BaseBackController;

/**
 * Site controller
 */
class SiteController extends BaseBackController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
				'access' => [
						'class' => AccessControl::className(),
						'only' => [
								'logout',
								'signup'
						],
						'rules' => [
								[
										'actions' => [
												'signup'
										],
										'allow' => true,
										'roles' => [
												'?'
										]
								],
								[
										'actions' => [
												'logout'
										],
										'allow' => true,
										'roles' => [
												'@'
										]
								]
						]
				],
				'verbs' => [
						'class' => VerbFilter::className(),
						'actions' => [
								'logout' => [
										'post'
								]
						]
				]
		];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
             ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null
            ]
        ];
    }

    public function actionIndex()
    {
//         $params = [];
// 		$params['boards'] = $this->buildBoards(0);
		
// 		return $this->render('index', $params);
		return $this->render('index');
    }

    public function actionLogin()
	{
		if (! \Yii::$app->user->isGuest)
		{
			return $this->goHome();
		}
		
		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login())
		{
			return $this->goBack();
		}
		else
		{
			return $this->render('login', 
					[
							'model' => $model
					]);
		}
	}

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
    public function actionSignup()
    {
        $model = new SignupForm();
		if ($model->load(Yii::$app->request->post()))
		{
			$user = $model->signup();
			if ($user)
			{
				if (Yii::$app->getUser()->login($user))
				{
					return $this->goHome();
				}
			}
		}
		
		return $this->render('signup', 
				[
						'model' => $model
				]);
    }
}
