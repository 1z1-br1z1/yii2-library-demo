<?php

namespace tests\unit\models;

use app\models\Author;
use app\models\BookForm;
use Codeception\Test\Unit;
use UnitTester;

class BookFormTest extends Unit
{
    public UnitTester $tester;

    private function validAttributes(): array
    {
        return [
            'name' => 'Название книги',
            'note' => 'Описание книги',
            'year' => 2020,
            'isbn' => '9780306406157',
        ];
    }

    // --- Required fields ---

    public function testRequiredFieldsFailWhenEmpty(): void
    {
        $form = new BookForm();

        verify($form->validate())->false();
        verify($form->errors)->arrayHasKey('name');
        verify($form->errors)->arrayHasKey('note');
        verify($form->errors)->arrayHasKey('year');
        verify($form->errors)->arrayHasKey('isbn');
        verify($form->errors)->arrayHasKey('authorsId');
    }

    // --- ISBN filter ---

    public function testIsbnFilterStripsDashes(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '978-0-306-40615-7']));
        $form->validate();
        verify($form->isbn)->equals('9780306406157');
    }

    public function testIsbnFilterUppercasesX(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '080442957x']));
        $form->validate();
        verify($form->isbn)->equals('080442957X');
    }

    // --- ISBN validation ---

    public function testIsbn10Valid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '0306406152']));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasNotKey('isbn');
    }

    public function testIsbn10WithXAtEndValid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '080442957X']));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasNotKey('isbn');
    }

    public function testIsbn13Valid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '9780306406157']));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasNotKey('isbn');
    }

    public function testIsbn9DigitsInvalid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '123456789']));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasKey('isbn');
    }

    public function testIsbn11DigitsInvalid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '12345678901']));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasKey('isbn');
    }

    public function testIsbnXNotAtEndInvalid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['isbn' => '080X42957X']));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasKey('isbn');
    }

    // --- Year ---

    public function testYearMinBoundaryValid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['year' => 1]));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasNotKey('year');
    }

    public function testYearZeroInvalid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['year' => 0]));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasKey('year');
    }

    public function testYearCurrentPlusOneValid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['year' => (int)date('Y') + 1]));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasNotKey('year');
    }

    public function testYearCurrentPlusTwoInvalid(): void
    {
        $form = new BookForm(array_merge($this->validAttributes(), ['year' => (int)date('Y') + 2]));
        $form->authorsId = [PHP_INT_MAX];
        $form->validate();
        verify($form->errors)->arrayHasKey('year');
    }

    // --- imageFile scenario ---

    public function testImageFileRequiredOnCreateScenario(): void
    {
        $form = new BookForm($this->validAttributes());
        $form->scenario = BookForm::SCENARIO_CREATE;
        $form->authorsId = [PHP_INT_MAX];
        $form->imageFile = null;

        $form->validate();
        verify($form->errors)->arrayHasKey('imageFile');
    }

    public function testImageFileNotRequiredOnDefaultScenario(): void
    {
        $form = new BookForm($this->validAttributes());
        $form->authorsId = [PHP_INT_MAX];
        $form->imageFile = null;

        $form->validate();
        verify($form->errors)->arrayHasNotKey('imageFile');
    }

    // --- authorsId existence ---

    public function testAuthorsIdNonExistentInvalid(): void
    {
        $form = new BookForm($this->validAttributes());
        $form->authorsId = [PHP_INT_MAX];

        $form->validate();
        verify($form->errors)->arrayHasKey('authorsId');
    }

    public function testAuthorsIdExistingValid(): void
    {
        $authorId = $this->tester->haveRecord(Author::class, ['fio' => 'Test Author']);

        $form = new BookForm($this->validAttributes());
        $form->authorsId = [$authorId];

        $form->validate();
        verify($form->errors)->arrayHasNotKey('authorsId');
    }
}
