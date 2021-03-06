<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Fixer\Import;

use PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Symplify\TokenRunner\Naming\Name\Name;
use Symplify\TokenRunner\Naming\Name\NameAnalyzer;
use Symplify\TokenRunner\Naming\Name\NameFactory;
use Symplify\TokenRunner\Naming\UseImport\UseImport;
use Symplify\TokenRunner\Naming\UseImport\UseImportsFactory;

/**
 * Possible cases.
 *
 * - 1. string that start with pre slash \SomeThing
 * - 2. namespace with conflicts \First\SomeClass + \Second\SomeClass
 * - 3. partial namespaces \Namespace\Partial + Partial\Class
 */
final class ImportNamespacedNameFixer implements FixerInterface, DefinedFixerInterface, ConfigurationDefinitionFixerInterface
{
    /**
     * @var string
     */
    private const ALLOW_SINGLE_NAMES_OPTION = 'allow_single_names';

    /**
     * @var int
     */
    private $namespacePosition;

    /**
     * @var UseImport[]
     */
    private $useImports = [];

    /**
     * @var mixed[]
     */
    private $configuration = [];

    public function __construct()
    {
        // set defaults
        $this->configuration = $this->getConfigurationDefinition()
            ->resolve([]);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Types should not be referenced via a fully/partially qualified name, but via a use statement.',
            [
                new CodeSample('<?php $value = \SomeNamespace\SomeClass'),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([T_CLASS, T_STRING]);
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $this->useImports = (new UseImportsFactory())->createForTokens($tokens);

        for ($index = $tokens->getSize() - 1; $index > 0; --$index) {
            $token = $tokens[$index];

            // Case 1.
            if (! NameAnalyzer::isImportableNameToken($tokens, $token, $index)) {
                continue;
            }

            $name = NameFactory::createFromTokensAndEnd($tokens, $index);
            if ($this->configuration[self::ALLOW_SINGLE_NAMES_OPTION] && $name->isSingleName()) {
                continue;
            }

            $name = $this->uniquateLastPart($name);

            // replace with last name part
            $tokens->overrideRange($name->getStart(), $name->getEnd(), [$name->getLastNameToken()]);

            // has this been already imported?
            if ($this->wasNameImported($name)) {
                continue;
            }

            if ($name->isPartialName()) {
                // add use statement
                $this->addIntoUseStatements($tokens, $name);

                continue;
            }

            // add use statement
            $this->addIntoUseStatements($tokens, $name);
        }
    }

    /**
     * Run before @see \PhpCsFixer\Fixer\Import\OrderedImportsFixer.
     */
    public function getPriority(): int
    {
        return -40;
    }

    /**
     * @param mixed[]|null $configuration
     */
    public function configure(?array $configuration = null): void
    {
        if ($configuration === null) {
            return;
        }

        $this->configuration = $this->getConfigurationDefinition()
            ->resolve($configuration);
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        $fixerOptionBuilder = new FixerOptionBuilder(
            self::ALLOW_SINGLE_NAMES_OPTION,
            'Whether allow \SingleClassName or import it.'
        );

        $singleNameOption = $fixerOptionBuilder->setAllowedValues([true, false])
            ->setDefault(false)
            ->getOption();

        return new FixerConfigurationResolver([$singleNameOption]);
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return self::class;
    }

    private function getNamespacePosition(Tokens $tokens): int
    {
        if ($this->namespacePosition) {
            return $this->namespacePosition;
        }

        $namespace = $tokens->findGivenKind(T_NAMESPACE);
        reset($namespace);

        return $this->namespacePosition = key($namespace);
    }

    /**
     * @param Token[] $nameTokens
     */
    private function addIntoUseStatements(Tokens $tokens, Name $name): void
    {
        $namespacePosition = $this->getNamespacePosition($tokens);
        $namespaceSemicolonPosition = $tokens->getNextTokenOfKind($namespacePosition, [';']);

        $tokens->insertAt($namespaceSemicolonPosition + 2, $name->getUseNameTokens());
    }

    private function wasNameImported(Name $name): bool
    {
        foreach ($this->useImports as $useImport) {
            if ($useImport->getFullName() === $name->getName()) {
                return true;
            }
        }

        $this->useImports[] = new UseImport($name->getName(), $name->getLastName());

        return false;
    }

    private function uniquateLastPart(Name $name): Name
    {
        foreach ($this->useImports as $useImport) {
            if ($useImport->getShortName() === $name->getLastName() && $useImport->getFullName() !== $name->getName()) {
                $uniquePrefix = $name->getFirstName();
                $name->addAlias($uniquePrefix . $name->getLastName());
            }
        }

        return $name;
    }
}
