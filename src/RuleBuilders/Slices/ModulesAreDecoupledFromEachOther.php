<?php

declare(strict_types=1);

namespace Arkitect\RuleBuilders\Slices;

use Arkitect\RuleBuilders\Architecture\Architecture;
use Arkitect\RuleBuilders\RuleSetBuilderInterface;

/**
 * @phpstan-type ExceptionList = array<string, array<string>>
 */
final class ModulesAreDecoupledFromEachOther implements RuleSetBuilderInterface
{
    private string $modulesBaseNamespace;
    private string $modulesBasePath;
    private array $exceptionList;
    private string $basePathGlob;

    /**
     * @phpstan-param ExceptionList $exceptionList A map of module name to the names of the modules it is allowed to depend on
     */
    public function __construct(
        string $modulesBaseNamespace,
        string $modulesBasePath,
        array $exceptionList = [],
        string $basePathGlob = '/*'
    ) {
        $this->modulesBaseNamespace = $modulesBaseNamespace;
        $this->modulesBasePath = $modulesBasePath;
        $this->exceptionList = $exceptionList;
        $this->basePathGlob = $basePathGlob;
    }

    public function build(): array
    {
        $modulesDirs = glob($this->modulesBasePath.$this->basePathGlob, \GLOB_ONLYDIR);
        if (false === $modulesDirs) {
            throw new \Exception("An error occurred while executing 'glob({$this->modulesBasePath}, GLOB_ONLYDIR)'");
        }
        $moduleNamespaceList = array_map(
            function (string $dir) {
                $positionOfLastNamespaceTokenBeginning = strrpos($this->modulesBaseNamespace, '\\');
                if (false === $positionOfLastNamespaceTokenBeginning) {
                    throw new \Exception("Could not find the namespace separator, in '{$this->modulesBaseNamespace}'");
                }
                $baseNamespaceToken = substr($this->modulesBaseNamespace, 0, $positionOfLastNamespaceTokenBeginning);
                $lastNamespaceToken = substr($this->modulesBaseNamespace, $positionOfLastNamespaceTokenBeginning);

                $namespacedDir = str_replace('/', '\\', $dir);
                $positionOfLastNamespaceDir = strrpos($namespacedDir, $lastNamespaceToken);
                if (false === $positionOfLastNamespaceDir) {
                    throw new \Exception("Could not find last namespace token '{$lastNamespaceToken}', in '{$namespacedDir}'");
                }

                return $baseNamespaceToken.'\\'.ltrim(substr($namespacedDir, $positionOfLastNamespaceDir), '\\');
            },
            $modulesDirs,
        );
        $moduleNameWithNamespaceList = [];
        foreach ($moduleNamespaceList as $moduleNamespace) {
            $moduleNameWithNamespaceList[$this->moduleNamespaceToModuleName($moduleNamespace)] = $moduleNamespace;
        }
        $moduleNamesList = array_keys($moduleNameWithNamespaceList);

        $moduleDependencyExceptions = $this->exceptionsNamespacesToModuleNames($this->exceptionList);

        /** @var Architecture $architecture */
        $architecture = Architecture::withComponents();

        foreach ($moduleNameWithNamespaceList as $moduleName => $moduleNamespace) {
            /** @var Architecture $architecture */
            $architecture = $architecture->component($moduleName)
                ->definedBy("{$moduleNamespace}\*")
                ->where($moduleName)->mustNotDependOnComponents(
                    ...array_diff($moduleNamesList, [$moduleName, ...array_values($moduleDependencyExceptions[$moduleName] ?? [])]),
                );
        }

        $iterableRules = $architecture->rules('modules should not depend on each other directly');
        $rulesList = [];
        foreach ($iterableRules as $rule) {
            $rulesList[] = $rule;
        }

        return $rulesList;
    }

    public function moduleNamespaceToModuleName(string $moduleNamespace): string
    {
        /** @var string $moduleName */
        $moduleName = str_replace($this->modulesBaseNamespace, '', $moduleNamespace);

        return trim($moduleName, '\\');
    }

    /**
     * @phpstan-param ExceptionList $exceptionList
     *
     * @phpstan-return ExceptionList
     */
    public function exceptionsNamespacesToModuleNames(array $exceptionList): array
    {
        $exceptionsModuleList = [];
        foreach ($exceptionList as $moduleNamespace => $moduleExceptionNamespaceList) {
            $moduleName = $this->moduleNamespaceToModuleName($moduleNamespace);
            foreach ($moduleExceptionNamespaceList as $moduleExceptionNamespace) {
                $exceptionsModuleList[$moduleName][] = $this->moduleNamespaceToModuleName($moduleExceptionNamespace);
            }
        }

        return $exceptionsModuleList;
    }
}
