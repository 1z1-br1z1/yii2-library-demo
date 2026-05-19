<?php

namespace app\controllers;

use app\models\BookForm;
use app\services\AuthorService;
use app\services\BookService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * BookController implements the CRUD actions for Book model.
 */
class BookController extends Controller {
    /**
     * @var BookService
     */
    private BookService $bookService;

    /**
     * @var AuthorService
     */
    private AuthorService $authorService;

    /**
     * @param $id
     * @param $module
     * @param BookService $bookService
     * @param AuthorService $authorService
     * @param $config
     */
    public function __construct($id, $module, BookService $bookService, AuthorService $authorService, $config = []) {
        parent::__construct($id, $module, $config);

        $this->bookService = $bookService;
        $this->authorService = $authorService;
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
                            'actions' => ['index', 'view'],
                            'allow' => true,
                            'roles' => ['?'],
                        ],
                        [
                            'actions' => ['index', 'view', 'create', 'update', 'delete'],
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
     * Lists all Book models.
     *
     * @return string
     */
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->bookService->getListQuery(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Book model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
            'model' => $this->bookService->getById($id, ['authors']),
            'user' => Yii::$app->getUser()
        ]);
    }

    /**
     * Creates a new Book model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $bookForm = new BookForm();
        $bookForm->scenario = BookForm::SCENARIO_CREATE;

        if ($this->request->isPost) {
            if ($bookForm->load($this->request->post())) {
                $bookForm->imageFile = UploadedFile::getInstance($bookForm, 'imageFile');

                if ($bookForm->validate()) {
                    $book = $this->bookService->create($bookForm);

                    return $this->redirect(['view', 'id' => $book->id]);
                }
            }
        } else {
            $bookForm->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $bookForm,
            'authors' => $this->authorService->getMap(),
        ]);
    }

    /**
     * Updates an existing Book model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $book = $this->bookService->getById($id, ['authors']);

        $bookForm = new BookForm($book->getAttributes());
        $bookForm->authorsId = array_map(static function ($author) {
            return $author->id;
        }, $book->authors);

        if ($this->request->isPost && $bookForm->load($this->request->post())) {
            $bookForm->imageFile = UploadedFile::getInstance($bookForm, 'imageFile');

            if ($bookForm->validate()) {
                $book = $this->bookService->update($book, $bookForm);

                return $this->redirect(['view', 'id' => $book->id]);
            }
        }

        return $this->render('update', [
            'model' => $bookForm,
            'authors' => $this->authorService->getMap(),
        ]);
    }

    /**
     * Deletes an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $book = $this->bookService->getById($id);
        $this->bookService->delete($book);

        return $this->redirect(['index']);
    }
}
