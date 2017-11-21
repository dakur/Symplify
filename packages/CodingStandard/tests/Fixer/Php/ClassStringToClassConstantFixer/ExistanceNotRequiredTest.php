<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Tests\Fixer\Php\ClassStringToClassConstantFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use Symplify\CodingStandard\Fixer\Php\ClassStringToClassConstantFixer;

final class ExistanceNotRequiredTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases()
     */
    public function testFix(string $expected, ?string $input = null): void
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
                file_get_contents(__DIR__ . '/fixed/fixed4.php.inc'),
                file_get_contents(__DIR__ . '/wrong/wrong4.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/fixed/fixed5.php.inc'),
                file_get_contents(__DIR__ . '/wrong/wrong5.php.inc'),
            ],
        ];
    }

    protected function createFixer(): FixerInterface
    {
        $classStringToClassConstantFixer = new ClassStringToClassConstantFixer();
        $classStringToClassConstantFixer->configure([
            'class_must_exist' => false,
        ]);

        return $classStringToClassConstantFixer;
    }
}
