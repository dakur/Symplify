<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Tests\Fixer\Naming\ExceptionNameFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use Symplify\CodingStandard\Fixer\Naming\ExceptionNameFixer;

final class Test extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases()
     */
    public function testFix(string $expected, string $input): void
    {
        $this->doTest($expected, $input);
    }

    /**
     * @return string[][]
     */
    public function provideFixCases(): array
    {
        return [
            [
                file_get_contents(__DIR__ . '/fixed/fixed.php.inc'),
                file_get_contents(__DIR__ . '/wrong/wrong.php.inc'),
            ],
        ];
    }

    protected function createFixer(): FixerInterface
    {
        return new ExceptionNameFixer();
    }
}
