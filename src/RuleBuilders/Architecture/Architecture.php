<?php
declare(strict_types=1);

namespace Arkitect\RuleBuilders\Architecture;

use Arkitect\Expression\Boolean\Orx;
use Arkitect\Expression\Expression;
use Arkitect\Expression\ForClasses\DependsOnlyOnTheseExpressions;
use Arkitect\Expression\ForClasses\NotDependsOnTheseExpressions;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;

class Architecture implements
    Component,
    DefinedBy,
    Where,
    MayDependOnComponents,
    MayDependOnAnyComponent,
    ShouldNotDependOnAnyComponent,
    ShouldOnlyDependOnComponents,
    MustNotDependOnComponents,
    Rules
{
    /** @var string */
    private $componentName;
    /** @var array<string, Expression|string> */
    private $componentSelectors;
    /** @var array<string, array<string>> */
    private $allowedDependencies;
    /** @var array<string, array<string>> */
    private $componentDependsOnlyOnTheseComponents;
    /** @var array<string, array<string>> */
    private $forbiddenDependencies;

    private function __construct()
    {
        $this->componentName = '';
        $this->componentSelectors = [];
        $this->allowedDependencies = [];
        $this->componentDependsOnlyOnTheseComponents = [];
        $this->forbiddenDependencies = [];
    }

    public static function withComponents(): Component
    {
        return new self();
    }

    public function component(string $name): DefinedBy
    {
        $this->componentName = $name;

        return $this;
    }

    public function definedBy(string $selector)
    {
        $this->componentSelectors[$this->componentName] = $selector;

        return $this;
    }

    public function definedByExpression(Expression $selector)
    {
        $this->componentSelectors[$this->componentName] = $selector;

        return $this;
    }

    public function where(string $componentName)
    {
        $this->componentName = $componentName;

        return $this;
    }

    public function shouldNotDependOnAnyComponent()
    {
        $this->allowedDependencies[$this->componentName] = [];

        return $this;
    }

    public function shouldOnlyDependOnComponents(string ...$componentNames)
    {
        $this->componentDependsOnlyOnTheseComponents[$this->componentName] = $componentNames;

        return $this;
    }

    public function mayDependOnComponents(string ...$componentNames)
    {
        $this->allowedDependencies[$this->componentName] = $componentNames;

        return $this;
    }

    public function mayDependOnAnyComponent()
    {
        $this->allowedDependencies[$this->componentName] = array_keys($this->componentSelectors);

        return $this;
    }

    public function mustNotDependOnComponents(string ...$componentNames)
    {
        $this->forbiddenDependencies[$this->componentName] = $componentNames;

        return $this;
    }

    public function rules(?string $because = null): iterable
    {
        foreach ($this->componentSelectors as $name => $selector) {
            if (isset($this->allowedDependencies[$name])) {
                $allowedDependenciesWithoutSelf = array_filter(
                    $this->allowedDependencies[$name],
                    function ($value) use ($name) {
                        return $value !== $name;
                    }
                );
                yield Rule::allClasses()
                    ->that(\is_string($selector) ? new ResideInOneOfTheseNamespaces($selector) : $selector)
                    ->should($this->createAllowedExpression(
                        array_merge([$name], $this->allowedDependencies[$name])
                    ))
                    ->because(
                        "$name can only depend on itself"
                        .(
                            \count($allowedDependenciesWithoutSelf)
                            ? ' and on '.implode(', ', $allowedDependenciesWithoutSelf)
                            : ''
                        )
                        .(\is_string($because) ? "\nbecause ".$because : '')
                    );
            }

            if (isset($this->componentDependsOnlyOnTheseComponents[$name])) {
                yield Rule::allClasses()
                    ->that(\is_string($selector) ? new ResideInOneOfTheseNamespaces($selector) : $selector)
                    ->should($this->createAllowedExpression($this->componentDependsOnlyOnTheseComponents[$name]))
                    ->because(
                        "$name can only depend on "
                        .implode(', ', $this->componentDependsOnlyOnTheseComponents[$name])
                        .(\is_string($because) ? "\nbecause ".$because : '')
                    );
            }

            if (isset($this->forbiddenDependencies[$name])) {
                yield Rule::allClasses()
                    ->that(\is_string($selector) ? new ResideInOneOfTheseNamespaces($selector) : $selector)
                    ->should($this->createForbiddenExpression($this->forbiddenDependencies[$name]))
                    ->because(
                        "$name must not depend on ".implode(', ', $this->forbiddenDependencies[$name])
                        .(\is_string($because) ? "\nbecause ".$because : '')
                    );
            }
        }
    }

    private function createForbiddenExpression(array $components): Expression
    {
        $namespaceSelectors = $this->extractComponentsNamespaceSelectors($components);

        $expressionSelectors = $this->extractComponentExpressionSelectors($components);

        if ([] === $namespaceSelectors && [] === $expressionSelectors) {
            return new Orx(); // always true
        }

        if ([] !== $namespaceSelectors) {
            $expressionSelectors[] = new ResideInOneOfTheseNamespaces(...$namespaceSelectors);
        }

        return new NotDependsOnTheseExpressions(...$expressionSelectors);
    }

    /**
     * @param array<string> $components
     */
    private function createAllowedExpression(array $components): Expression
    {
        $namespaceSelectors = $this->extractComponentsNamespaceSelectors($components);

        $expressionSelectors = $this->extractComponentExpressionSelectors($components);

        if ([] === $namespaceSelectors && [] === $expressionSelectors) {
            return new Orx(); // always true
        }

        if ([] !== $namespaceSelectors) {
            $expressionSelectors[] = new ResideInOneOfTheseNamespaces(...$namespaceSelectors);
        }

        return new DependsOnlyOnTheseExpressions(...$expressionSelectors);
    }

    private function extractComponentsNamespaceSelectors(array $components): array
    {
        return array_values(array_filter(
            array_map(function (string $componentName): ?string {
                $selector = $this->componentSelectors[$componentName];

                return \is_string($selector) ? $selector : null;
            }, $components)
        ));
    }

    private function extractComponentExpressionSelectors(array $components): array
    {
        return array_values(array_filter(
            array_map(function (string $componentName): ?Expression {
                $selector = $this->componentSelectors[$componentName];

                return \is_string($selector) ? null : $selector;
            }, $components)
        ));
    }
}
