<?php

declare(strict_types=1);

namespace Tests\src;

use WalkWeb\NW\Runtime;
use Tests\AbstractTestCase;

class RuntimeTest extends AbstractTestCase
{
    public function testRuntime(): void
    {
        $runtime = new Runtime();

        // Из-за разного контекста вызова теста (отдельно или в общем запуске всех тестов) runtime будет сильно различаться
        // По этому просто проверяем, что получен float.
        self::assertIsFloat($runtime->getRuntime());
        // Расход будет отличаться и расход памяти: в районе от 0 до 450000 байт
        self::assertTrue($runtime->getMemoryCost() < 5000000);
        // Что именно выведет заранее не предсказать, это может быть и "0 b" и, например "60.77 kb"
        self::assertIsString($runtime->getMemoryCostClipped());
        // Просто проверяем, что получена строка
        self::assertMatchesRegularExpression('/Runtime: /', $runtime->getStatistic());
        self::assertMatchesRegularExpression('/ ms, memory cost: /', $runtime->getStatistic());
    }
}
