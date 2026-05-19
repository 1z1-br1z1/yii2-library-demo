<?php

namespace tests\unit\services;

use app\jobs\NotifyAuthorSubscribersJob;
use app\models\Author;
use app\models\Book;
use app\models\BookForm;
use app\services\BookService;
use app\services\UploadedFileService;
use Codeception\Test\Unit;
use UnitTester;
use Yii;
use yii\base\Component;
use yii\queue\db\Queue;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class BookServiceTest extends Unit
{
    public UnitTester $tester;

    private BookService $service;
    /** @var UploadedFileService|\PHPUnit\Framework\MockObject\MockObject */
    private $uploadService;
    private SpyQueue $queue;
    private static int $isbnSeq = 0;

    protected function _before(): void
    {
        Yii::setAlias('@webroot', Yii::getAlias('@app') . '/web');

        $this->uploadService = $this->createMock(UploadedFileService::class);
        $this->uploadService->method('save')->willReturn('/uploads/test.jpg');

        $this->service = new BookService($this->uploadService);

        $this->queue = new SpyQueue();
        Yii::$app->set('queue', $this->queue);
    }

    // --- Helpers ---

    private function makeAuthor(string $fio = 'Test Author'): Author
    {
        $id = $this->tester->haveRecord(Author::class, ['fio' => $fio]);
        return Author::findOne($id);
    }

    private function makeForm(array $authorIds, array $attrs = []): BookForm
    {
        $form = new BookForm(array_merge([
            'name' => 'Test Book',
            'note' => 'Description',
            'year' => 2020,
            'isbn' => str_pad(++self::$isbnSeq, 13, '0', STR_PAD_LEFT),
        ], $attrs));
        $form->authorsId = $authorIds;
        $form->imageFile = $this->createMock(UploadedFile::class);
        return $form;
    }

    private function makeTempPhoto(): string
    {
        $webroot = sys_get_temp_dir() . '/yii2test_' . uniqid();
        mkdir($webroot . '/uploads', 0777, true);
        Yii::setAlias('@webroot', $webroot);

        $relPath = '/uploads/tmp_' . uniqid() . '.jpg';
        file_put_contents($webroot . $relPath, 'fake image data');
        return $relPath;
    }

    // --- create() ---

    public function testCreatePersistsBookInDatabase(): void
    {
        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id], ['name' => 'My Book', 'year' => 2023]));

        verify($book->isNewRecord)->false();
        verify(Book::findOne($book->id))->notNull();
        verify($book->name)->equals('My Book');
        verify($book->year)->equals(2023);
    }

    public function testCreateSetsPhotoFromUploadService(): void
    {
        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id]));

        verify($book->photo)->equals('/uploads/test.jpg');
    }

    public function testCreateLinksAuthors(): void
    {
        $authorA = $this->makeAuthor('Author A');
        $authorB = $this->makeAuthor('Author B');

        $book = $this->service->create($this->makeForm([$authorA->id, $authorB->id]));
        $book->refresh();

        $linkedIds = array_map(fn(Author $a) => $a->id, $book->authors);
        verify($linkedIds)->arrayContains($authorA->id);
        verify($linkedIds)->arrayContains($authorB->id);
    }

    public function testCreatePushesJobForEachAuthor(): void
    {
        $authorA = $this->makeAuthor('Author A');
        $authorB = $this->makeAuthor('Author B');

        $this->service->create($this->makeForm([$authorA->id, $authorB->id]));

        verify(count($this->queue->pushedJobs))->equals(2);
    }

    public function testCreateJobsContainCorrectAuthorIds(): void
    {
        $authorA = $this->makeAuthor('Author A');
        $authorB = $this->makeAuthor('Author B');

        $this->service->create($this->makeForm([$authorA->id, $authorB->id]));

        $pushedIds = array_map(fn(NotifyAuthorSubscribersJob $j) => $j->authorId, $this->queue->pushedJobs);
        verify($pushedIds)->arrayContains($authorA->id);
        verify($pushedIds)->arrayContains($authorB->id);
    }

    // --- update() ---

    public function testUpdateChangesAttributes(): void
    {
        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id], ['name' => 'Old Name', 'year' => 2000]));

        $updateForm = $this->makeForm([$author->id], ['name' => 'New Name', 'year' => 2024]);
        $updateForm->imageFile = null;
        $this->service->update($book, $updateForm);
        $book->refresh();

        verify($book->name)->equals('New Name');
        verify($book->year)->equals(2024);
    }

    public function testUpdateAddsNewAuthorAndPushesJob(): void
    {
        $authorA = $this->makeAuthor('Author A');
        $authorB = $this->makeAuthor('Author B');

        $book = $this->service->create($this->makeForm([$authorA->id]));
        $this->queue->pushedJobs = [];

        $updateForm = $this->makeForm([$authorA->id, $authorB->id]);
        $updateForm->imageFile = null;
        $this->service->update($book, $updateForm);

        verify(count($this->queue->pushedJobs))->equals(1);
        verify($this->queue->pushedJobs[0]->authorId)->equals($authorB->id);
    }

    public function testUpdateRemovesAuthorWithoutPushingJob(): void
    {
        $authorA = $this->makeAuthor('Author A');
        $authorB = $this->makeAuthor('Author B');

        $book = $this->service->create($this->makeForm([$authorA->id, $authorB->id]));
        $this->queue->pushedJobs = [];

        $updateForm = $this->makeForm([$authorA->id]);
        $updateForm->imageFile = null;
        $this->service->update($book, $updateForm);

        verify(count($this->queue->pushedJobs))->equals(0);
        $book->refresh();
        $linkedIds = array_map(fn(Author $a) => $a->id, $book->authors);
        verify($linkedIds)->arrayNotContains($authorB->id);
    }

    public function testUpdateUnchangedAuthorsDoNotPushJobs(): void
    {
        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id]));
        $this->queue->pushedJobs = [];

        $updateForm = $this->makeForm([$author->id], ['name' => 'Renamed']);
        $updateForm->imageFile = null;
        $this->service->update($book, $updateForm);

        verify(count($this->queue->pushedJobs))->equals(0);
    }

    public function testUpdateWithNewImageFileUpdatesPhoto(): void
    {
        // Reinitialize with sequential return values: first call is for create(), second for update()
        $this->uploadService = $this->createMock(UploadedFileService::class);
        $this->uploadService->method('save')
            ->willReturnOnConsecutiveCalls('/uploads/initial.jpg', '/uploads/updated.jpg');
        $this->service = new BookService($this->uploadService);

        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id]));

        $updateForm = $this->makeForm([$author->id]);
        $updateForm->imageFile = $this->createMock(UploadedFile::class);
        $this->service->update($book, $updateForm);

        verify($book->photo)->equals('/uploads/updated.jpg');
    }

    public function testUpdateWithoutNewImageFileKeepsExistingPhoto(): void
    {
        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id]));
        $originalPhoto = $book->photo;

        $updateForm = $this->makeForm([$author->id], ['name' => 'Renamed']);
        $updateForm->imageFile = null;
        $this->service->update($book, $updateForm);

        verify($book->photo)->equals($originalPhoto);
    }

    // --- delete() ---

    public function testDeleteRemovesBookFromDatabase(): void
    {
        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id]));

        $book->photo = $this->makeTempPhoto();
        $book->save(false);
        $bookId = $book->id;

        $this->service->delete($book);

        verify(Book::findOne($bookId))->null();
    }

    public function testDeleteRemovesPhotoFile(): void
    {
        $author = $this->makeAuthor();
        $book = $this->service->create($this->makeForm([$author->id]));

        $photoRelPath = $this->makeTempPhoto();
        $book->photo = $photoRelPath;
        $book->save(false);

        $this->service->delete($book);

        verify(file_exists(Yii::getAlias('@webroot') . $photoRelPath))->false();
    }

    // --- getById() ---

    public function testGetByIdReturnsCorrectBook(): void
    {
        $author = $this->makeAuthor();
        $created = $this->service->create($this->makeForm([$author->id], ['name' => 'Find Me']));

        $found = $this->service->getById($created->id);

        verify($found->id)->equals($created->id);
        verify($found->name)->equals('Find Me');
    }

    public function testGetByIdThrowsNotFoundHttpException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->service->getById(PHP_INT_MAX);
    }

    public function testGetByIdEagerLoadsRequestedRelations(): void
    {
        $author = $this->makeAuthor();
        $created = $this->service->create($this->makeForm([$author->id]));

        $book = $this->service->getById($created->id, ['authors']);

        // Relation is already loaded — no extra query should be needed
        verify($book->isRelationPopulated('authors'))->true();
        verify($book->authors[0]->id)->equals($author->id);
    }

    // --- getYears() ---

    public function testGetYearsReturnsDistinctYearsDescending(): void
    {
        $author = $this->makeAuthor();
        $this->service->create($this->makeForm([$author->id], ['year' => 2021]));
        $this->service->create($this->makeForm([$author->id], ['year' => 2023]));
        $this->service->create($this->makeForm([$author->id], ['year' => 2021])); // duplicate

        $years = $this->service->getYears();

        verify(in_array(2021, $years))->true();
        verify(in_array(2023, $years))->true();
        verify(count(array_keys($years, 2021)))->equals(1);
        verify(array_values($years))->equals([2023, 2021]);
    }
}

class SpyQueue extends Component
{
    public array $pushedJobs = [];

    public function push($job): string
    {
        $this->pushedJobs[] = $job;
        return uniqid();
    }
}
