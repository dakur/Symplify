<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Tests\Fixer\Property\ArrayPropertyDefaultValueFixer;

use PhpCsFixer\Fixer\FixerInterface;
use Symplify\CodingStandard\Fixer\Property\ArrayPropertyDefaultValueFixer;
use Symplify\TokenRunner\Testing\AbstractSimpleFixerTestCase;

final class Test extends AbstractSimpleFixerTestCase
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
            [__DIR__ . '/fixed/fixed.php.inc', __DIR__ . '/wrong/wrong.php.inc', ],
            [__DIR__ . '/fixed/fixed2.php.inc', __DIR__ . '/wrong/wrong2.php.inc', ],
            [__DIR__ . '/fixed/fixed3.php.inc', __DIR__ . '/wrong/wrong3.php.inc', ],
            [__DIR__ . '/fixed/fixed4.php.inc', __DIR__ . '/wrong/wrong4.php.inc', ],
            [__DIR__ . '/correct/correct.php.inc', ], ];
    }

    protected function createFixer(): FixerInterface
    {
        return new ArrayPropertyDefaultValueFixer();
    }
}
