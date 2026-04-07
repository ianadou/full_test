<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\StudentSorter;
use PHPUnit\Framework\TestCase;

final class StudentSorterTest extends TestCase
{
    private StudentSorter $sorter;

    protected function setUp(): void
    {
        $this->sorter = new StudentSorter();
    }

    public function test_sorts_students_by_grade_ascending(): void
    {
        // ARRANGE
        $students = [
            ['name' => 'Alice', 'grade' => 14, 'age' => 22],
            ['name' => 'Bob', 'grade' => 8, 'age' => 20],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 21],
        ];

        // ACT
        $result = $this->sorter->sort($students, 'grade');

        // ASSERT
        $this->assertSame('Bob', $result[0]['name']);
        $this->assertSame('Alice', $result[1]['name']);
        $this->assertSame('Charlie', $result[2]['name']);
    }
        public function test_sorts_students_by_grade_descending(): void
    {
        // ARRANGE
        $students = [
            ['name' => 'Alice', 'grade' => 14, 'age' => 22],
            ['name' => 'Bob', 'grade' => 8, 'age' => 20],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 21],
        ];

        // ACT
        $result = $this->sorter->sort($students, 'grade', 'desc');

        // ASSERT
        $this->assertSame('Charlie', $result[0]['name']);
        $this->assertSame('Alice', $result[1]['name']);
        $this->assertSame('Bob', $result[2]['name']);
    }

    public function test_sorts_students_by_name_ascending(): void
    {
        // ARRANGE
        $students = [
            ['name' => 'Charlie', 'grade' => 18, 'age' => 21],
            ['name' => 'Alice', 'grade' => 14, 'age' => 22],
            ['name' => 'Bob', 'grade' => 8, 'age' => 20],
        ];

        // ACT
        $result = $this->sorter->sort($students, 'name');

        // ASSERT
        $this->assertSame('Alice', $result[0]['name']);
        $this->assertSame('Bob', $result[1]['name']);
        $this->assertSame('Charlie', $result[2]['name']);
    }

    public function test_sorts_students_by_age_ascending(): void
    {
        // ARRANGE
        $students = [
            ['name' => 'Alice', 'grade' => 14, 'age' => 22],
            ['name' => 'Bob', 'grade' => 8, 'age' => 20],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 21],
        ];

        // ACT
        $result = $this->sorter->sort($students, 'age');

        // ASSERT
        $this->assertSame('Bob', $result[0]['name']);
        $this->assertSame('Charlie', $result[1]['name']);
        $this->assertSame('Alice', $result[2]['name']);
    }

    public function test_returns_empty_array_when_null_input(): void
    {
        // ACT
        $result = $this->sorter->sort([], 'grade');

        // ASSERT
        $this->assertSame([], $result);
    }

    public function test_returns_empty_array_when_empty_input(): void
    {
        // ACT
        $result = $this->sorter->sort([], 'grade');

        // ASSERT
        $this->assertSame([], $result);
    }

    public function test_does_not_modify_original_array(): void
    {
        // ARRANGE
        $students = [
            ['name' => 'Bob', 'grade' => 8, 'age' => 20],
            ['name' => 'Alice', 'grade' => 14, 'age' => 22],
        ];
        $original = $students;

        // ACT
        $this->sorter->sort($students, 'grade');

        // ASSERT
        $this->assertSame($original, $students);
    }

    public function test_defaults_to_ascending_order(): void
    {
        // ARRANGE
        $students = [
            ['name' => 'Bob', 'grade' => 14, 'age' => 20],
            ['name' => 'Alice', 'grade' => 8, 'age' => 22],
        ];

        // ACT
        $result = $this->sorter->sort($students, 'grade');

        // ASSERT
        $this->assertSame('Alice', $result[0]['name']);
        $this->assertSame('Bob', $result[1]['name']);
    }

}
