<?php declare(strict_types=1);

namespace Symplify\TokenRunner\Wrapper\FixerWrapper;

use PhpCsFixer\Tokenizer\Tokens;
use Symplify\TokenRunner\Guard\TokenTypeGuard;

final class ArgumentWrapper extends AbstractVariableWrapper
{
    public static function createFromTokensAndPosition(Tokens $tokens, int $position): self
    {
        TokenTypeGuard::ensureIsTokenType($tokens[$position], [T_VARIABLE], __METHOD__);

        return new self($tokens, $position);
    }

    public function changeName(string $newName): void
    {
        $this->changeNameWithTokenType($newName, T_VARIABLE);
    }

    protected function getNamePosition(): ?int
    {
        return $this->index;
    }
}
