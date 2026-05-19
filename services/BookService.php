<?php

namespace app\services;

use app\jobs\NotifyAuthorSubscribersJob;

use app\models\Author;
use app\models\Book;
use app\models\BookForm;
use app\models\BookQuery;

use Throwable;
use Yii;
use yii\web\NotFoundHttpException;

class BookService {
    /**
     * @var UploadedFileService
     */
    private UploadedFileService $uploadedFileService;

    /**
     * @param UploadedFileService $uploadedFileService
     */
    public function __construct(UploadedFileService $uploadedFileService) {
        $this->uploadedFileService = $uploadedFileService;
    }

    public function create(BookForm $bookForm): Book {
        $addedAuthorIds = null;
        $transaction = Yii::$app->getDb()->beginTransaction();

        try {
            $book = new Book($bookForm->getAttributes());
            $book->photo = $this->uploadedFileService->save($bookForm->imageFile);
            $book->save(false);
            $addedAuthorIds = $this->syncAuthors($book, $bookForm);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($addedAuthorIds) {
            foreach ($addedAuthorIds as $authorId) {
                Yii::$app->get('queue')->push(new NotifyAuthorSubscribersJob([
                    'authorId' => $authorId,
                ]));
            }
        }

        return $book;
    }

    public function update(Book $book, BookForm $bookForm): Book {
        $addedAuthorIds = null;
        $transaction = Yii::$app->getDb()->beginTransaction();

        try {
            $book->setAttributes($bookForm->getAttributes(['name', 'note', 'year', 'isbn']));
            if ($bookForm->imageFile) {
                $book->photo = $this->uploadedFileService->save($bookForm->imageFile);
            }
            $book->save(false);
            $addedAuthorIds = $this->syncAuthors($book, $bookForm);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($addedAuthorIds) {
            foreach ($addedAuthorIds as $authorId) {
                Yii::$app->get('queue')->push(new NotifyAuthorSubscribersJob([
                    'authorId' => $authorId,
                ]));
            }
        }

        return $book;
    }

    private function syncAuthors(Book $book, BookForm $bookForm): array {
        $oldIds = array_map(static function (Author $author) {
            return $author->id;
        }, $book->authors);

        if ($deletedIds = array_diff($oldIds, $bookForm->authorsId)) {
            Yii::$app->getDb()->createCommand()->delete('book2author', [
                'bookId' => $book->id,
                'authorId' => $deletedIds,
            ])->execute();
        }

        if ($addedIds = array_diff($bookForm->authorsId, $oldIds)) {
            $rows = [];

            foreach($addedIds as $authorId) {
                $rows[] = [$book->id, $authorId];
            }

            Yii::$app->getDb()->createCommand()->batchInsert('book2author', ['bookId', 'authorId'], $rows)->execute();
        }

        foreach (Author::find()->where(['id' => $addedIds])->all() as $author) {
            $book->link('authors', $author);
        }

        return $addedIds;
    }

    public function getListQuery(): BookQuery {
        return Book::find()->with('authors');
    }

    public function getById(int $id, ?array $with = null): Book {
        $query = Book::find()->where(['id' => $id]);

        if ($with) {
            $query->with($with);
        }

        if (empty($model = $query->one())) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $model;
    }

    /**
     * @param Book $book
     */
    public function delete(Book $book) {
        if($book->delete()){
            unlink(Yii::getAlias('@webroot'). $book->photo);
        }
    }

    public function getYears(): array {
        return Book::find()->select('year')->distinct()->orderBy(['year' => SORT_DESC])->indexBy('year')->column();
    }
}
