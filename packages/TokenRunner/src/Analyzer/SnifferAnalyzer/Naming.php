<?php declare(strict_types=1);

namespace Symplify\TokenRunner\Analyzer\SnifferAnalyzer;

use Nette\Utils\Strings;
use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\ReferencedNameHelper;
use SlevomatCodingStandard\Helpers\UseStatementHelper;
use Symplify\TokenRunner\Guard\TokenTypeGuard;

final class Naming
{
    /**
     * @var string
     */
    private const NAMESPACE_SEPARATOR = '\\';

    /**
     * @var string[]
     */
    private static $controllerNameSuffixes = ['Controller', 'Presenter'];

    public static function isControllerClass(File $file, int $position): bool
    {
        TokenTypeGuard::ensureIsTokenType($file->getTokens()[$position], [T_CLASS], __METHOD__);

        $className = $file->getDeclarationName($position);
        if (! $className) {
            return false;
        }

        foreach (self::$controllerNameSuffixes as $controllerNameSuffix) {
            if (Strings::endsWith($className, $controllerNameSuffix)) {
                return true;
            }
        }

        return false;
    }

    public static function getClassName(File $file, int $classNameStartPosition): string
    {
        $tokens = $file->getTokens();

        $classNameParts = [];
        $classNameParts[] = $tokens[$classNameStartPosition]['content'];

        $nextTokenPointer = $classNameStartPosition + 1;
        while ($tokens[$nextTokenPointer]['code'] === T_NS_SEPARATOR) {
            ++$nextTokenPointer;
            $classNameParts[] = $tokens[$nextTokenPointer]['content'];
            ++$nextTokenPointer;
        }

        $completeClassName = implode(self::NAMESPACE_SEPARATOR, $classNameParts);

        $fqnClassName = self::getFqnClassName($file, $completeClassName, $classNameStartPosition);
        if ($fqnClassName) {
            return ltrim($fqnClassName, self::NAMESPACE_SEPARATOR);
        }

        return $completeClassName;
    }

    private static function getFqnClassName(File $file, string $className, int $classTokenPosition): string
    {
        $useStatements = UseStatementHelper::getUseStatements($file, 0);
        $referencedNames = ReferencedNameHelper::getAllReferencedNames($file, 0);

        foreach ($referencedNames as $referencedName) {
            $resolvedName = NamespaceHelper::resolveClassName(
                $file,
                $referencedName->getNameAsReferencedInFile(),
                $useStatements,
                $classTokenPosition
            );

            if ($referencedName->getNameAsReferencedInFile() === $className) {
                return $resolvedName;
            }
        }

        return '';
    }
}
