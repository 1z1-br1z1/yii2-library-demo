<?php

namespace app\controllers;

use app\models\Author;
use app\models\SubscriptionForm;
use app\services\AuthorService;
use app\services\BookService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * AuthorController implements the CRUD actions for Author model.
 */
class AuthorController extends Controller {
    private AuthorService $authorService;

    private BookService $bookService;

    public function __construct($id, $module, AuthorService $authorService, BookService $bookService, $config = []) {
        parent::__construct($id, $module, $config);

        $this->authorService = $authorService;
        $this->bookService = $bookService;
    }

    /**
     * @inheritDoc
     */
    public function behaviors() {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'actions' => ['index', 'view', 'top', 'subscribe'],
                            'allow' => true,
                            'roles' => ['?'],
                        ],
                        [
                            'actions' => ['index', 'view', 'top', 'create', 'update', 'delete'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Author models.
     *
     * @return string
     */
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->authorService->getListQuery(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Author model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $model = $this->authorService->getById($id, ['books']);

        return $this->render('view', [
            'model' => $model,
            'user' => Yii::$app->getUser()
        ]);
    }

    /**
     * Creates a new Author model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new Author();
        $request = $this->request;

        if ($request->isPost) {
            if ($model->load($request->post()) && $model->validate()) {
                $this->authorService->create($model);

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Author model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->authorService->getById($id);
        $request = $this->request;

        if ($request->isPost && $model->load($request->post()) && $model->validate()) {
            $this->authorService->update($model);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Author model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $model = $this->authorService->getById($id);
        $this->authorService->delete($model);

        return $this->redirect(['index']);
    }

    public function actionSubscribe($id) {
        $model = $this->authorService->getById($id);
        $subscription = new SubscriptionForm([
            'authorId' => $model->id,
        ]);
        $request = $this->request;

        if ($request->isPost && $subscription->load($request->post()) && $subscription->validate()) {
            $this->authorService->subscribe($model, $subscription);

            Yii::$app->getSession()->setFlash('success', 'Вы успешно подписаны на автора.');

            return $this->redirect(['view', 'id' => $subscription->authorId]);
        }

        return $this->render('subscribe', [
            'model' => $subscription,
        ]);
    }

    /**
     * Top 10 authors by book count for a given year.
     *
     * @return string
     */
    public function actionTop(?string $year = null) {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $this->authorService->getTop($year ? (int)$year : null),
            'sort' => [
                'attributes' => ['id', 'fio', 'count'],
            ],
        ]);

        return $this->render('top', [
            'year' => $year,
            'years' => $this->bookService->getYears(),
            'dataProvider' => $dataProvider,
        ]);
    }
}
