<?php declare(strict_types=1);

namespace Symplify\TokenRunner\Naming\Name;

use Nette\Utils\Strings;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\TokenRunner\Naming\UseImport\UseImport;
use Symplify\TokenRunner\Naming\UseImport\UseImportsFactory;

final class Name
{
    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $end;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Token[]
     */
    private $nameTokens = [];

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var UseImport|null
     */
    private $relatedUseImport;

    /**
     * @var Tokens
     */
    private $tokens;

    /**
     * @param Token[] $nameTokens
     */
    public function __construct(int $start, int $end, string $name, array $nameTokens, Tokens $tokens)
    {
        $this->start = $start;
        $this->end = $end;
        $this->name = $name;
        $this->nameTokens = $nameTokens;
        $this->lastName = $this->nameTokens[count($this->nameTokens) - 1]->getContent();
        $this->tokens = $tokens;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLastName(): string
    {
        if ($this->alias) {
            return $this->alias;
        }

        return $this->lastName;
    }

    public function addAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    public function getFirstName(): string
    {
        return $this->nameTokens[0]->getContent();
    }

    /**
     * @return Token[]
     */
    public function getUseNameTokens(): array
    {
        $tokens = [];

        $tokens[] = new Token([T_USE, 'use']);
        $tokens[] = new Token([T_WHITESPACE, ' ']);

        if ($this->relatedUseImport) {
            $startName = $this->nameTokens[0]->getContent();

            foreach ($this->relatedUseImport->getNameParts() as $useDeclarationPart) {
                if ($useDeclarationPart === $startName) {
                    break;
                }

                $tokens[] = new Token([T_STRING, $useDeclarationPart]);
                $tokens[] = new Token([T_NS_SEPARATOR, '\\']);
            }
        }

        $tokens = array_merge($tokens, $this->nameTokens);

        if ($this->alias) {
            $tokens[] = new Token([T_WHITESPACE, ' ']);
            $tokens[] = new Token([T_AS, 'as']);
            $tokens[] = new Token([T_WHITESPACE, ' ']);
            $tokens[] = new Token([T_STRING, $this->alias]);
        }

        $tokens[] = new Token(';');
        $tokens[] = new Token([T_WHITESPACE, PHP_EOL]);

        return $tokens;
    }

    public function getLastNameToken(): Token
    {
        return new Token([T_STRING, $this->getLastName()]);
    }

    public function setRelatedUseImport(UseImport $useImport): void
    {
        $this->relatedUseImport = $useImport;
    }

    public function isSingleName(): bool
    {
        return count($this->nameTokens) === 1;
    }

    public function isPartialName(): bool
    {
        if (Strings::startsWith($this->name, '\\')) {
            return false;
        }

        if (! Strings::contains($this->name, '\\')) {
            return false;
        }

        $useImports = (new UseImportsFactory())->createForTokens($this->tokens);

        foreach ($useImports as $useImport) {
            if ($useImport->startsWith($this->name)) {
                $this->relatedUseImport = $useImport;

                return true;
            }
        }

        return false;
    }
}
