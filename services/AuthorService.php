<?php

namespace app\services;

use app\models\Author;
use app\models\AuthorQuery;
use app\models\Subscription;
use PDO;
use yii\web\NotFoundHttpException;

class AuthorService {
    public function create(Author $author): bool {
        return $author->save(false);
    }

    public function update(Author $author): bool {
        return $author->save(false);
    }

    public function delete(Author $author) {
        return $author->delete();
    }

    public function subscribe(Author $author, Subscription $subscription) {
        $author->link('subscriptions', $subscription);
    }

    public function getTop(?int $year = null): array {
        $query = Author::find()
            ->select('{{author}}.[[id]], {{author}}.[[fio]], COUNT({{author}}.[[id]]) AS [[count]]')
            ->joinWith('books', false, 'INNER JOIN')
            ->groupBy('{{author}}.[[id]]')
            ->orderBy(['count' => SORT_DESC])
            ->limit(10);

        if (isset($year)) {
            $query->where(['{{book}}.[[year]]' => $year]);
        }

        return $query->asArray()->all();
    }

    public function getListQuery(): AuthorQuery {
        return Author::find()->with('books');
    }

    public function getById(int $id, array $with = []): Author {
        $query = Author::find()->where(['id' => $id]);

        if ($with) {
            $query->with($with);
        }

        if (empty($model = $query->one())) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $model;
    }

    public function getMap(): array {
        return Author::find()->select(['id', 'fio'])->createCommand()->queryAll(PDO::FETCH_KEY_PAIR);
    }
}
