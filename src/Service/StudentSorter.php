<?php

declare(strict_types=1);

namespace App\Service;

final class StudentSorter
{
    /**
     * @param array<array{name: string, grade: int|float, age: int}> $students
     *
     * @return array<array{name: string, grade: int|float, age: int}>
     */
    public function sort(array $students, string $sortBy, string $order = 'asc'): array
    {
        $sorted = $students;

        usort($sorted, function (array $a, array $b) use ($sortBy, $order): int {
            $comparison = $a[$sortBy] <=> $b[$sortBy];

            return 'desc' === $order ? -$comparison : $comparison;
        });

        return $sorted;
    }
}
