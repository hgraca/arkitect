<?php
declare(strict_types=1);

namespace Arkitect\RuleBuilders\Architecture;

use Arkitect\Expression\Boolean\Andx;
use Arkitect\Expression\Boolean\Not;
use Arkitect\Expression\Boolean\Orx;
use Arkitect\Expression\Expression;
use Arkitect\Expression\ForClasses\DependsOnlyOnTheseExpressions;
use Arkitect\Expression\ForClasses\DependsOnlyOnTheseNamespaces;
use Arkitect\Expression\ForClasses\NotDependsOnTheseNamespaces;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;

class Architecture implements Component, DefinedBy, Where, MayDependOnComponents, MayDependOnAnyComponent, ShouldNotDependOnAnyComponent, ShouldOnlyDependOnComponents, Rules
{
    /** @var string */
    private $componentName;
    /** @var array<string, string> */
    private $componentSelectors;
    /** @var array<string, string[]> */
    private $allowedDependencies;
    /** @var array<string, string[]> */
    private $componentDependsOnlyOnTheseNamespaces;

    private function __construct()
    {
        $this->componentName = '';
        $this->componentSelectors = [];
        $this->allowedDependencies = [];
        $this->componentDependsOnlyOnTheseNamespaces = [];
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
        $this->componentDependsOnlyOnTheseNamespaces[$this->componentName] = $componentNames;

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

    public function rules(): iterable
    {
        $layerNames = array_keys($this->componentSelectors);

        foreach ($this->componentSelectors as $name => $selector) {
            if (isset($this->allowedDependencies[$name])) {
                $forbiddenComponents = array_diff($layerNames, [$name], $this->allowedDependencies[$name]);

                if (!empty($forbiddenComponents)) {
                    yield Rule::allClasses()
                        ->that(\is_string($selector) ? new ResideInOneOfTheseNamespaces($selector) : $selector)
                        ->should($this->createForbiddenExpression($forbiddenComponents))
                        ->because('of component architecture');
                }
            }

            if (!isset($this->componentDependsOnlyOnTheseNamespaces[$name])) {
                continue;
            }

            yield Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces($selector))
                ->should($this->createAllowedExpression($this->componentDependsOnlyOnTheseNamespaces[$name]))
                ->because('of component architecture');
        }
    }

    private function createForbiddenExpression(array $components): Expression
    {
        $namespaceSelectors = $this->extractComponentsNamespaceSelectors($components);

        $expressionSelectors = $this->extractComponentExpressionSelectors($components);

        $expressionList = [];
        if ($namespaceSelectors !== []) {
            $expressionList[] = new NotDependsOnTheseNamespaces(...$namespaceSelectors);
        }
        if ($expressionSelectors !== []) {
            $expressionList[] = new Not(new Orx(...$expressionSelectors));
        }

        return count($expressionList) === 1
            ? array_pop($expressionList)
            : new Andx(...$expressionList);
    }

    private function createAllowedExpression(array $components): Expression
    {
        $namespaceSelectors = $this->extractComponentsNamespaceSelectors($components);

        $expressionSelectors = $this->extractComponentExpressionSelectors($components);

        if ($namespaceSelectors === [] && $expressionSelectors === []) {
            return new Orx(); // always true
        }

        if ($namespaceSelectors !== []) {
            $expressionSelectors[] = new ResideInOneOfTheseNamespaces(...$namespaceSelectors);
        }

        return new DependsOnlyOnTheseExpressions(...$expressionSelectors);
    }

    private function extractComponentsNamespaceSelectors(array $components): array
    {
        return array_filter(
            array_map(function (string $componentName): ?string {
                $selector = $this->componentSelectors[$componentName];
                return \is_string($selector) ? $selector : null;
            }, $components)
        );
    }

    public function extractComponentExpressionSelectors(array $components): array
    {
        return array_filter(
            array_map(function (string $componentName): ?Expression {
                $selector = $this->componentSelectors[$componentName];
                return \is_string($selector) ? null : $selector;
            }, $components)
        );
    }
}
