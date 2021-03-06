<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Tests\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\WhitespacesFixerConfig;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;

final class Test extends AbstractFixerTestCase
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
                file_get_contents(__DIR__ . '/fixed/fixed.php.inc'),
                file_get_contents(__DIR__ . '/wrong/wrong.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/fixed/fixed2.php.inc'),
                file_get_contents(__DIR__ . '/wrong/wrong2.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/fixed/fixed3.php.inc'),
                file_get_contents(__DIR__ . '/wrong/wrong3.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/fixed/fixed4.php.inc'),
                file_get_contents(__DIR__ . '/wrong/wrong4.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/correct/correct.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/correct/correct2.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/correct/correct3.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/correct/correct4.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/correct/correct5.php.inc'),
            ],
            [
                file_get_contents(__DIR__ . '/correct/correct6.php.inc'),
            ],
            ['<?php $emotions = [1 => \'Happy\'];'],
            ['<?php $emotions = [\'Happy\', \'Excited\'];'],
        ];
    }

    protected function createFixer(): FixerInterface
    {
        $standaloneLineInMultilineArrayFixer = new StandaloneLineInMultilineArrayFixer();
        $standaloneLineInMultilineArrayFixer->setWhitespacesConfig($this->createWhitespacesFixerConfig());

        return $standaloneLineInMultilineArrayFixer;
    }

    private function createWhitespacesFixerConfig(): WhitespacesFixerConfig
    {
        return new WhitespacesFixerConfig('    ', PHP_EOL);
    }
}
